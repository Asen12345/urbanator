<?php

namespace App\Services;

use App\Interfaces\FaqInterface;
use CIBlockElement;

class FaqService implements FaqInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFaqItems(int $iblockId): array
    {
        if ($iblockId <= 0) {
            throw new \Exception('Некорректный ID инфоблока', 400);
        }

        // Формирование базового фильтра для CIBlockElement
        $arFilter = [
            "IBLOCK_ID" => $iblockId,
            "ACTIVE"    => 'Y',
        ];

        // Параметры выборки
        $arSelect = [
            "ID",
            "NAME",
            "PREVIEW_TEXT",
        ];

        // Получаем элементы инфоблока
        $result = [];
        $rsElements = CIBlockElement::GetList(
            ["SORT" => "ASC"], // Сортировка по полю SORT
            $arFilter,
            false,
            false,
            $arSelect
        );

        while ($element = $rsElements->GetNext()) {
            $result[] = [
                'id' => $element['ID'],
                'name' => $element['NAME'],
                'preview_text' => $element['PREVIEW_TEXT'],
            ];
        }

        return $result;
    }
}