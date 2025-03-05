<?php

namespace App\Controllers;

use App\Services\CatalogService;
use App\Validators\CatalogValidator;
use App\Interfaces\CatalogInterface;

class CatalogController extends AbstractController
{
    protected CatalogInterface $catalogService;
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
        $filters = $this->getPostData();

        $this->validate($filters, $this->validator);

        $filters['page']  = isset($filters['page']) ? (int)$filters['page'] : 1;
        $filters['limit'] = isset($filters['limit']) ? (int)$filters['limit'] : 10;

        $result = $this->catalogService->getCatalog($filters);

        return $result;
    }
}
