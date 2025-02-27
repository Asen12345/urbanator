<?php

namespace App\Services;

use App\Interfaces\CategoryInterface;
use App\Enums\Category\CategoryFields;
use App\Services\IblockSelectorService;

class CategoryService implements CategoryInterface
{
    private $selectedIblockId = null;
    private $fieldsSelect = [];

    public function __construct()
    {
        $iblockSelector = new IblockSelectorService();
        $this->selectedIblockId = $iblockSelector();

        $this->fieldsSelect = [
            CategoryFields::NAME->value,
            CategoryFields::CODE->value,
            CategoryFields::PICTURE->value,
            CategoryFields::DESCRIPTION->value,
            CategoryFields::DETAIL_PICTURE->value,
            CategoryFields::ID->value,
        ];
    }

    public function getSubcategories(int $categoryId): array
    {
        $subcategories = [];

        if ($this->selectedIblockId === null)
            throw new \Exception('Ошибка с id каталога', 400);

        $arFilter = [
            "IBLOCK_ID" => $this->selectedIblockId,
            "ACTIVE" => 'Y',
            "SECTION_ID" => $categoryId
        ];
        $rsSubcategories = \CIBlockSection::GetList(["SORT" => "ASC"], $arFilter, false, $this->fieldsSelect);

        while ($arSubcategory = $rsSubcategories->GetNext()) {
            $subcategories[] = $this->formatCategoryData($arSubcategory);
        }

        return $subcategories;
    }

    public function getCategories(): array
    {
        $categories = [];

        if ($this->selectedIblockId === null)
            throw new \Exception('Ошибка с id каталога', 400);

        $arFilter = [
            "IBLOCK_ID" => $this->selectedIblockId,
            "ACTIVE" => 'Y',
            "SECTION_ID" => false
        ];
        $rsCategories = \CIBlockSection::GetList(["SORT" => "ASC"], $arFilter, false, $this->fieldsSelect);

        while ($arCategory = $rsCategories->fetch()) {
            $categories[] = $this->formatCategoryData($arCategory);
        }

        return $categories;
    }

    private function formatCategoryData($arCategory): array
    {
        return [
            "ID"          => $arCategory['ID'],
            "NAME"        => $arCategory['NAME'],
            "CODE"        => $arCategory['CODE'],
            "PICTURE"     => \CFile::GetPath($arCategory['PICTURE']),
            "DESCRIPTION" => $arCategory['DESCRIPTION'] ?? null, // Описание может отсутствовать
            "DETAIL_PICTURE" => \CFile::GetPath($arCategory['DETAIL_PICTURE'] ?? null),
        ];
    }
}
