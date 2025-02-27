<?php

namespace App\Controllers;

use App\Services\ProductService;
use App\Validators\ProductValidator;

class ProductController
{
    protected ProductValidator $validator;
    protected ProductService $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->validator = new ProductValidator();
    }

    public function getDetail(): array
    {
        $this->validator->validate($_GET);

        $productId = (int)$_GET['id'];
        $product = $this->productService->getProduct($productId);

        return $product;
    }
}
