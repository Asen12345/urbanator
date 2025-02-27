<?php

namespace App\Interfaces;

interface CategoryInterface
{
    public function getCategories(): array;
    public function getSubcategories(int $categoryId): array;
}
