<?php

namespace App\Controllers;

use App\Services\ProductService;
use App\Validators\ProductValidator;
use App\Interfaces\ProductInterface;

class ProductController extends AbstractController
{
    protected ProductValidator $validator;
    protected ProductInterface $productService;

    public function __construct()
    {
        $this->productService = new ProductService();
        $this->validator = new ProductValidator();
    }

    public function getDetail(): array
    {
        $data = $this->getGetData();

        $this->validate($data, $this->validator);

        $productId = (int)$data['id'];
        $product = $this->productService->getProduct($productId);

        return $product;
    }
}
