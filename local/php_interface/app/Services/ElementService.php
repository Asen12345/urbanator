<?php

namespace App\Services;

use App\Interfaces\ElementInterface;

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
} 