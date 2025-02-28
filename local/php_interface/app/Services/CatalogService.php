<?php

namespace App\Services;

use App\Interfaces\CatalogInterface;
use App\Services\ProductFormatterService;
use App\Config\ConfigLoader;

class CatalogService implements CatalogInterface
{
    protected ?int $selectedIblockId;
    protected ?int $selectedIblockIdOffer;
    protected array $filterProperties;
    protected ProductFormatterService $formatter;

    public function __construct()
    {
        $configLoader = new ConfigLoader();

        $this->filterProperties = $configLoader->get('catalog_filter_properties', []);
        $this->selectedIblockId = $configLoader->get('catalog_id', 0);
        $this->selectedIblockIdOffer = $configLoader->get('offers_id', 0);
        $this->formatter = new ProductFormatterService();
    }

    /**
     * Получает каталог товаров по фильтрам.
     *
     * @param array $filters
     * @return array
     */
    public function getCatalog(array $filters): array
    {
        if ($this->selectedIblockId === null) {
            throw new \Exception('Ошибка с id инфоблока каталога продуктов', 400);
        }

        // Формирование базового фильтра для CIBlockElement по товарам
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

        // Обработка фильтрации по свойствам
        $offerFilter = null;
        if (!empty($filters['properties']) && is_array($filters['properties'])) {
            // Создаем фильтр для торговых предложений
            $offerFilter = [
                "IBLOCK_ID" => $this->selectedIblockIdOffer,
                "ACTIVE"    => 'Y',
            ];
            
            foreach ($filters['properties'] as $propCode => $propValue) {
                // Определяем, к какой сущности относится свойство (товар или торговое предложение)
                $entityType = $this->getPropertyEntityType($propCode);
                
                if ($entityType === 'product') {
                    // Добавляем свойство в фильтр товаров
                    $arFilter['PROPERTY_' . $propCode] = $propValue;
                } elseif ($entityType === 'offer') {
                    // Добавляем свойство в фильтр торговых предложений
                    $offerFilter['PROPERTY_' . $propCode] = $propValue;
                }
            }
            
            // Если есть фильтры по торговым предложениям, получаем ID товаров с подходящими предложениями
            if ($offerFilter && count($offerFilter) > 2) { // Если есть дополнительные условия кроме IBLOCK_ID и ACTIVE
                $productIds = $this->getProductIdsWithMatchingOffers($offerFilter);
                if (!empty($productIds)) {
                    // Если уже есть фильтр по ID товаров, делаем пересечение
                    if (isset($arFilter['ID'])) {
                        $arFilter['ID'] = array_intersect((array)$arFilter['ID'], $productIds);
                        if (empty($arFilter['ID'])) {
                            // Если пересечение пустое, возвращаем пустой результат
                            return [
                                'products'        => [],
                                'total_pages'     => 0,
                                'min_price'       => 0,
                                'max_price'       => 0,
                                'property_values' => [],
                            ];
                        }
                    } else {
                        $arFilter['ID'] = $productIds;
                    }
                } else {
                    // Если нет товаров с подходящими предложениями, возвращаем пустой результат
                    return [
                        'products'        => [],
                        'total_pages'     => 0,
                        'min_price'       => 0,
                        'max_price'       => 0,
                        'property_values' => [],
                    ];
                }
            }
        }

        if (isset($filters['limit'], $filters['page'])) {
            $limit = (int)$filters['limit'];
            $page = (int)$filters['page'];
            $navParams = ['nPageSize' => $limit, 'iNumPage' => $page];
        } else {
            $navParams = ['nPageSize' => 10, 'iNumPage' => 1];
        }

        // Получаем список элементов (товаров) с пагинацией
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

        // Определяем минимальную и максимальную цены
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

        // Собираем значения фильтруемых свойств с учетом применённых фильтров.
        // Теперь для каждого свойства передаём тип сущности ("product" или "offer")
        $propertyValues = [];
        foreach ($this->filterProperties as $group => $properties) {
            foreach ($properties as $propertyData) {
                // $propertyData имеет структуру: [<filterType>, <propertyCode>, <entity>]
                $propertyCode = $propertyData[1];
                $filterType = $propertyData[0];
                $entity = $propertyData[2];
                
                // Получаем доступные значения свойства с учетом текущих фильтров
                $values = $this->getFilteredPropertyValues(
                    $propertyCode,
                    $arFilter,
                    $filterType,
                    $entity
                );
                
                // Добавляем информацию о типе сущности для фронтенда
                $propertyValues[$propertyCode] = [
                    'values' => $values,
                    'entity' => $entity
                ];
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
     * Получает значения фильтруемого свойства для торговых предложений.
     * Сначала получаем все ID торговых предложений, удовлетворяющие базовому фильтру,
     * затем выбираем по этим ID значения свойства.
     *
     * @param string $propertyCode Код свойства, например "PROPERTY_UPPERMATERIAL"
     * @param string $filterType Тип свойства ("link" или "list")
     * @param array $baseOfferFilter Базовый фильтр для торговых предложений.
     * @return array Массив уникальных значений.
     */
    private function getFilteredOfferPropertyValues(string $propertyCode, string $filterType, array $baseOfferFilter): array
    {
        $values = [];

        // Создаем копию фильтра и добавляем условие на наличие значения свойства
        $offerFilter = $baseOfferFilter;
        $offerFilter['!' . $propertyCode] = false;

        $dbRes = \CIBlockElement::GetList(
            [], // стандартная сортировка
            $offerFilter,
            false,
            false,
            [$propertyCode]
        );

        while ($element = $dbRes->Fetch()) {
            $fieldKey = $propertyCode . '_VALUE';
            if (!empty($element[$fieldKey])) {
                if ($filterType === 'link') {
                    $values[] = $this->getLinkedElementName($element[$fieldKey]);
                } elseif ($filterType === 'list') {
                    $values[] = $this->getEnumValueName($propertyCode, $element[$fieldKey]);
                } else {
                    $values[] = $element[$fieldKey];
                }
            }
        }

        return array_unique($values);
    }

    /**
     * Универсальный метод получения значений фильтруемых свойств.
     * Делегирует вызов для товаров или торговых предложений.
     *
     * @param string $propertyCode Пример: "PROPERTY_BRAND"
     * @param array  $currentFilter Базовый фильтр для товаров.
     * @param string $filterType Тип свойства ("link" или "list")
     * @param string $entity "product" или "offer"
     * @return array Массив уникальных значений свойства.
     */
    private function getFilteredPropertyValues(string $propertyCode, array $currentFilter, string $filterType, string $entity = 'product'): array
    {
        if ($entity === 'offer') {
            // Для торговых предложений используем базовый фильтр с активностью и инфоблоком предложений.
            $baseOfferFilter = [
                "IBLOCK_ID" => $this->selectedIblockIdOffer,
                "ACTIVE"    => 'Y',
                // При необходимости можно добавить и другие условия.
            ];
            
            // Если в текущем фильтре есть ID товаров, добавляем их в фильтр торговых предложений
            if (!empty($currentFilter['ID'])) {
                // Получаем только торговые предложения для отфильтрованных товаров
                $baseOfferFilter['PROPERTY_CML2_LINK'] = $currentFilter['ID'];
            } elseif (!empty($currentFilter['SECTION_ID'])) {
                // Если задана категория, получаем ID товаров из этой категории
                $productIds = $this->getProductIdsFromCategory($currentFilter['SECTION_ID'], $currentFilter['INCLUDE_SUBSECTIONS'] ?? 'N');
                if (!empty($productIds)) {
                    $baseOfferFilter['PROPERTY_CML2_LINK'] = $productIds;
                }
            }

            return $this->getFilteredOfferPropertyValues($propertyCode, $filterType, $baseOfferFilter);
        } else {
            return $this->getFilteredProductPropertyValues($propertyCode, $currentFilter, $filterType);
        }
    }

    /**
     * Получает значения фильтруемого свойства для товаров.
     *
     * @param string $propertyCode Пример: "PROPERTY_BRAND"
     * @param array  $currentFilter Базовый фильтр для товаров.
     * @param string $filterType Тип свойства ("link" или "list")
     * @return array Массив уникальных значений.
     */
    private function getFilteredProductPropertyValues(string $propertyCode, array $currentFilter, string $filterType): array
    {
        $values = [];
        $filter = $currentFilter;
        $filter['IBLOCK_ID'] = $this->selectedIblockId;
        $filter['!' . $propertyCode . '_VALUE'] = false;

        $dbRes = \CIBlockElement::GetList(
            [],
            $filter,
            false,
            false,
            [$propertyCode]
        );

        while ($element = $dbRes->Fetch()) {
            $fieldKey = $propertyCode . '_VALUE';
            if (!empty($element[$fieldKey])) {
                if ($filterType === 'link') {
                    $values[] = $this->getLinkedElementName($element[$fieldKey]);
                } elseif ($filterType === 'list') {
                    $values[] = $this->getEnumValueName($propertyCode, $element[$fieldKey]);
                } else {
                    $values[] = $element[$fieldKey];
                }
            }
        }

        return array_unique($values);
    }

    /**
     * Получает имя связанного элемента по его ID.
     *
     * @param int|string $elementId
     * @return string Имя элемента.
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
     * Получает человекочитаемое значение для перечисляемого свойства.
     *
     * @param string $propertyCode Пример: "PROPERTY_SoleS"
     * @param mixed  $enumValue Значение перечисления (обычно ID)
     * @return string Значение перечисления.
     */
    private function getEnumValueName(string $propertyCode, $enumValue): string
    {
        $propCode = str_replace('PROPERTY_', '', $propertyCode);
        $rsEnum = \CIBlockPropertyEnum::GetList(
            [],
            ['PROPERTY_CODE' => $propCode, 'ID' => $enumValue]
        );
        if ($enum = $rsEnum->Fetch()) {
            return $enum['VALUE'];
        }
        return (string)$enumValue;
    }

    /**
     * Определяет, к какой сущности относится свойство (товар или торговое предложение)
     *
     * @param string $propCode Код свойства
     * @return string 'product' или 'offer'
     */
    private function getPropertyEntityType(string $propCode): string
    {
        // Проверяем в конфигурации, к какой сущности относится свойство
        foreach ($this->filterProperties as $group => $properties) {
            foreach ($properties as $propertyData) {
                // $propertyData имеет структуру: [<filterType>, <propertyCode>, <entity>]
                $configPropCode = str_replace('PROPERTY_', '', $propertyData[1]);
                if ($configPropCode === $propCode) {
                    return $propertyData[2]; // Возвращаем тип сущности ('product' или 'offer')
                }
            }
        }
        
        // По умолчанию считаем, что свойство относится к товару
        return 'product';
    }

    /**
     * Получает ID товаров, у которых есть торговые предложения, соответствующие фильтру
     *
     * @param array $offerFilter Фильтр для торговых предложений
     * @return array Массив ID товаров
     */
    private function getProductIdsWithMatchingOffers(array $offerFilter): array
    {
        $productIds = [];
        
        // Получаем все торговые предложения, соответствующие фильтру
        $dbRes = \CIBlockElement::GetList(
            [],
            $offerFilter,
            false,
            false,
            ['ID', 'PROPERTY_CML2_LINK']
        );
        
        while ($offer = $dbRes->Fetch()) {
            if (!empty($offer['PROPERTY_CML2_LINK_VALUE'])) {
                $productIds[] = $offer['PROPERTY_CML2_LINK_VALUE'];
            }
        }
        
        return array_unique($productIds);
    }

    /**
     * Получает ID товаров из категории.
     *
     * @param int $categoryId ID категории
     * @param string $includeSubcategories Флаг включения подкатегорий
     * @return array Массив ID товаров
     */
    private function getProductIdsFromCategory(int $categoryId, string $includeSubcategories): array
    {
        $productIds = [];
        $filter = [
            "IBLOCK_ID" => $this->selectedIblockId,
            "SECTION_ID" => $categoryId,
            "ACTIVE" => 'Y',
        ];
        
        if ($includeSubcategories === 'Y') {
            $filter["INCLUDE_SUBSECTIONS"] = 'Y';
        }
        
        $res = \CIBlockElement::GetList(
            [],
            $filter,
            false,
            false,
            ['ID']
        );

        while ($element = $res->Fetch()) {
            $productIds[] = $element['ID'];
        }

        return array_unique($productIds);
    }
}
