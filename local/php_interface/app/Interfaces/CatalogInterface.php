<?php

namespace App\Interfaces;

interface CatalogInterface
{
    /**
     * Получает набор товаров по фильтрам.
     *
     * Ожидаемые параметры фильтра:
     * - category_id: int|null
     * - price_min: float|null
     * - price_max: float|null
     * - query: string|null
     * - page: int (номер страницы)
     * - limit: int (кол-во на странице)
     * - properties: array (ключ => значение, например, 'PROPERTY_BRAND' => 'Samsung')
     * - in_stock: bool|null (фильтрация по наличию)
     *
     * На выводе возвращается массив с ключами:
     * - products: [ отформатированные товары (через ProductFormatterService) ]
     * - property_values: список возможных значений для каждого фильтруемого свойства
     * - min_price: минимальная цена среди товаров
     * - max_price: максимальная цена среди товаров
     * - total_pages: общее число страниц
     *
     * @param array $filters
     * @return array
     */
    public function getCatalog(array $filters): array;
}