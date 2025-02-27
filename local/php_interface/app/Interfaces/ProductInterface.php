<?php

namespace App\Interfaces;

interface ProductInterface
{
    public function getProduct(int $productId): array;
}