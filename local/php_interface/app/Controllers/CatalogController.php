<?php

namespace App\Controllers;

use App\Services\CatalogService;
use App\Validators\CatalogValidator;

class CatalogController
{
    protected CatalogService $catalogService;
    protected CatalogValidator $validator;

    public function __construct()
    {
        $this->catalogService = new CatalogService();
        $this->validator = new CatalogValidator();
    }

    /**
     * Получение каталога товаров по фильтрам.
     *
     * Ожидается, что входные параметры передаются через GET:
     * category_id, price_min, price_max, query, page, limit, properties, in_stock.
     *
     * @return array
     */
    public function getCatalog(): array
    {
        // Получаем данные из GET (или другого источника)
        $data = $_POST;

        // Валидируем входные параметры
        $filters = $this->validator->validate($data);

        $result = $this->catalogService->getCatalog($filters);

        return $result;
    }
}