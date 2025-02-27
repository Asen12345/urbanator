<?php

namespace App\Services;

use App\Interfaces\CatalogInterface;
use App\Services\IblockSelectorService;
use App\Services\ProductFormatterService;
use App\Config\ConfigLoader;

class CatalogService implements CatalogInterface
{
    protected ?int $selectedIblockId;
    protected array $filterProperties;
    protected ProductFormatterService $formatter;

    public function __construct()
    {
        $iblockSelector = new IblockSelectorService();
        $this->selectedIblockId = $iblockSelector();
        $configLoader = new ConfigLoader();
        // Получаем настройки фильтруемых свойств для каталога
        $this->filterProperties = $configLoader->get('catalog_filter_properties', []);
        $this->formatter = new ProductFormatterService();
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalog(array $filters): array
    {
        if ($this->selectedIblockId === null) {
            throw new \Exception('Ошибка с id инфоблока каталога продуктов', 400);
        }

        // Формирование базового фильтра для CIBlockElement
        $arFilter = [
            "IBLOCK_ID" => $this->selectedIblockId,
            "ACTIVE"    => 'Y',
        ];

        // Если задана категория – нужно включить и подкатегории
        if (!empty($filters['category_id'])) {
            $arFilter['SECTION_ID'] = (int)$filters['category_id'];
            $arFilter['INCLUDE_SUBSECTIONS'] = 'Y';
        }

        // Фильтр по поисковой строке (подстрока в названии)
        if (!empty($filters['query'])) {
            $arFilter['%NAME'] = $filters['query'];
        }

        // Фильтрация по наличию товара
        if (isset($filters['in_stock']) && $filters['in_stock'] === true) {
            $arFilter['AVAILABLE'] = 'Y';
        }

        // Фильтрация по цене, если заданы price_min и price_max
        if (isset($filters['price_min'])) {
            $arFilter['>=CATALOG_PRICE_1'] = (float)$filters['price_min'];
        }
        if (isset($filters['price_max'])) {
            $arFilter['<=CATALOG_PRICE_1'] = (float)$filters['price_max'];
        }

        if (isset($filters['limit']) && isset($filters['page'])) {
            $limit = (int)$filters['limit'];
            $page = (int)$filters['page'];
            $navParams = ['nPageSize' => $limit, 'iNumPage' => $page];
        } else {
            $navParams = ['nPageSize' => 10, 'iNumPage' => 1];
        }

        // Получим список всех элементов по базовому фильтру с пагинацией
        $elements = [];
        $res = \CIBlockElement::GetList(
            ["SORT" => "ASC"],
            $arFilter,
            false,
            $navParams,
            ['ID']
        );

        while ($element = $res->Fetch()) {
            $item = $this->formatter->__invoke((int)$element['ID']);
            $elements[] = $item;
        }

        $totalPages = $navParams ? (int)$res->NavPageCount : 1;

        // Определяем минимальную и максимальную цены среди отфильтрованных товаров
        $res = \CIBlockElement::GetList(
            ["CATALOG_PRICE_1" => "ASC"],
            $arFilter,
            false,
            ['nPageSize' => 1, 'iNumPage' => 1],
            ['CATALOG_PRICE_1']
        );
        $minPriceData = $res->Fetch();
        $minPrice = (int)$minPriceData['CATALOG_PRICE_1'];

        $res = \CIBlockElement::GetList(
            ["CATALOG_PRICE_1" => "DESC"],
            $arFilter,
            false,
            ['nPageSize' => 1, 'iNumPage' => 1],
            ['CATALOG_PRICE_1']
        );
        $maxPriceData = $res->Fetch();
        $maxPrice = (int)$maxPriceData['CATALOG_PRICE_1'];

        // Собираем значения фильтруемых свойств с учетом примененных фильтров
        $propertyValues = [];
        foreach ($this->filterProperties as $group => $properties) {
            foreach ($properties as $propertyCode) {
                $propertyValues[$propertyCode[1]] = $this->getFilteredPropertyValues($propertyCode[1], $arFilter, $propertyCode[0]);
            }
        }

        return [
            'products'        => $elements,
            'total_pages'     => $totalPages,
            'min_price'       => $minPrice,
            'max_price'       => $maxPrice,
            'property_values' => $propertyValues,
        ];
    }

    /**
     * Fetch distinct values for a given property code considering current filters and property type.
     *
     * @param string $propertyCode Example: "PROPERTY_BRAND"
     * @param array  $currentFilter The current CIBlock filter.
     * @param string $propType      Either "link" or "list"
     * @return array
     */
    private function getFilteredPropertyValues(string $propertyCode, array $currentFilter, string $propType): array
    {
        $values = [];
        // NOTE: When selecting properties, CIBlockElement returns the property value in a key
        // like "PROPERTY_BRAND_VALUE". We use that key here.
        $dbRes = \CIBlockElement::GetList(
            [],
            // We add the requirement that the property exists. This helps filter only products containing the property.
            array_merge($currentFilter, ['!' . $propertyCode . '_VALUE' => false]),
            false,
            false,
            [$propertyCode]
        );

        while ($element = $dbRes->Fetch()) {
            $fieldKey = $propertyCode . '_VALUE';
            if (!empty($element[$fieldKey])) {
                if ($propType === 'link') {
                    // For linked properties, the stored value is the ID of the linked element.
                    $values[] = $this->getLinkedElementName($element[$fieldKey]);
                } elseif ($propType === 'list') {
                    // For enumerated properties, get the human-readable enum value.
                    $values[] = $this->getEnumValueName($propertyCode, $element[$fieldKey]);
                } else {
                    $values[] = $element[$fieldKey];
                }
            }
        }

        return array_unique($values);
    }

    /**
     * Retrieves the name of a linked element given its ID.
     *
     * @param int|string $elementId
     * @return string
     */
    private function getLinkedElementName($elementId): string
    {
        $res = \CIBlockElement::GetList(
            [],
            ['ID' => $elementId, 'ACTIVE' => 'Y'],
            false,
            ['nPageSize' => 1],
            ['ID', 'NAME']
        );
        if ($element = $res->Fetch()) {
            return $element['NAME'];
        }
        return '';
    }

    /**
     * Retrieves the human-readable value for an enumerated property.
     *
     * @param string $propertyCode Example: "PROPERTY_SoleS"
     * @param mixed  $enumValue    The stored enumeration value (usually the ID)
     * @return string
     */
    private function getEnumValueName(string $propertyCode, $enumValue): string
    {
        // Remove the "PROPERTY_" prefix (if needed) to use in filters.
        $propCode = str_replace('PROPERTY_', '', $propertyCode);
        $rsEnum = \CIBlockPropertyEnum::GetList(
            [],
            ['PROPERTY_CODE' => $propCode, 'ID' => $enumValue]
        );
        if ($enum = $rsEnum->Fetch()) {
            return $enum['VALUE'];
        }
        // Fallback: if no enum found, return the stored value.
        return (string)$enumValue;
    }
}
