<?php

namespace App\Services;

use App\Interfaces\ElementInterface;
use App\Enums\Element\IblockTypes;

class ElementService implements ElementInterface
{
    /**
     * Поиск элемента в инфоблоке по коду
     *
     * @param int $iblockId ID инфоблока
     * @param string $code Код элемента
     * @return array|null Массив с данными элемента или null, если элемент не найден
     */
    public function getElementByCode(int $iblockId, string $code): ?array
    {
        if (empty($iblockId) || empty($code)) {
            return null;
        }

        $arFilter = [
            "IBLOCK_ID" => $iblockId,
            "CODE" => $code,
            "ACTIVE" => "Y"
        ];

        $arSelect = [
            "ID",
            "NAME",
            "CODE",
            "DETAIL_TEXT"
        ];

        $rsElement = \CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            ["nTopCount" => 1],
            $arSelect
        );

        if ($arElement = $rsElement->GetNext()) {
            return [
                "ID" => $arElement["ID"],
                "NAME" => $arElement["NAME"],
                "CODE" => $arElement["CODE"],
                "DETAIL_TEXT" => $arElement["DETAIL_TEXT"]
            ];
        }

        return null;
    }

    /**
     * Получение всех элементов из инфоблока с указанным ID
     *
     * @param int $iblockId ID инфоблока
     * @return array Массив с элементами инфоблока
     */
    public function getAllElementsFromIblock(int $iblockId): array
    {
        if (empty($iblockId)) {
            return [];
        }

        $arFilter = [
            "IBLOCK_ID" => $iblockId,
            "ACTIVE" => "Y"
        ];

        $arSelect = [
            "ID",
            "NAME",
            "PREVIEW_PICTURE"
        ];

        $rsElements = \CIBlockElement::GetList(
            ["SORT" => "ASC"],
            $arFilter,
            false,
            false,
            $arSelect
        );

        $elements = [];
        while ($arElement = $rsElements->GetNext()) {
            // Получаем URL изображения, если оно есть
            $previewPicture = null;
            if (!empty($arElement["PREVIEW_PICTURE"])) {
                $previewPicture = \CFile::GetPath($arElement["PREVIEW_PICTURE"]);
            }

            $elements[] = [
                "ID" => $arElement["ID"],
                "NAME" => $arElement["NAME"],
                "PREVIEW_PICTURE" => $previewPicture
            ];
        }

        return $elements;
    }

    /**
     * Получение товаров из свойства FILTER_NEW элемента
     *
     * @param int $elementId ID элемента
     * @return array Массив с ID товаров
     */
    private function getProductsFromFilterNew(int $elementId): array
    {
        if (empty($elementId)) {
            return [];
        }

        // Получаем свойство FILTER_NEW элемента
        $rsProps = \CIBlockElement::GetProperty(
            IblockTypes::SPECIAL_IBLOCK,
            $elementId,
            [],
            ["CODE" => "FILTER_NEW"]
        );

        $productIds = [];
        if ($arProp = $rsProps->Fetch()) {
            $property = json_decode($arProp["VALUE"]);
            $productIds = $property->CHILDREN[0]->DATA->value;
        }

        return $productIds;
    }

    public function getFormattedProductsFromFilterNew(int $elementId, int $page = 1, int $perPage = 20): array
    {
        $productIds = $this->getProductsFromFilterNew($elementId);

        if (empty($productIds)) {
            return [
                'products' => [],
                'total_pages' => 0,
                'total_products' => 0
            ];
        }

        $totalProducts = count($productIds);
        $totalPages = ceil($totalProducts / $perPage);

        $formatterService = new ProductFormatterService();
        $formattedProducts = [];

        // Пагинация
        $offset = ($page - 1) * $perPage;
        $pagedProductIds = array_slice($productIds, $offset, $perPage);

        foreach ($pagedProductIds as $productId) {
            try {
                $formattedProducts[] = $formatterService((int)$productId);
            } catch (\Exception $e) {
                // Пропускаем товары, которые не удалось отформатировать
                continue;
            }
        }

        return [
            'products' => $formattedProducts,
            'total_pages' => $totalPages,
            'total_products' => $totalProducts
        ];
    }
}
