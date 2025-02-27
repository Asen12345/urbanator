<?php

namespace App\Services;

class IblockSelectorService
{
    /**
     * Получает id инфоблока для товаров и категорий
     */
    public function __invoke(): ?int
    {
        $data = \Bitrix\Catalog\CatalogIblockTable::getList([
            'filter' => ['PRODUCT_IBLOCK_ID' => 0]
        ])->fetchAll();

        $maxCount = 0;
        $selectedIblockId = null;
        foreach ($data as $iblock) {
            $elementCount = \CIBlockElement::GetList([], ['IBLOCK_ID' => $iblock['IBLOCK_ID']], [], false, []);
            if ($elementCount > $maxCount) {
                $maxCount = $elementCount;
                $selectedIblockId = $iblock['IBLOCK_ID'];
            }
        }
        return $selectedIblockId;
    }
}
