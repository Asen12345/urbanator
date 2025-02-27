<?php

namespace App\Controllers;

use App\Services\CategoryService;
use App\Validators\CategoryValidator;

class CategoryController
{
    protected CategoryService $categoryService;
    protected CategoryValidator $validator;

    public function __construct()
    {
        $this->categoryService =  new CategoryService();
        $this->validator     = new CategoryValidator();
    }

    public function getList(): array
    {
        $this->validator->validate($_GET);

        // Получаем список категорий через сервис
        $categories = $this->categoryService->getCategories();


        // Возвращаем данные (контроллер может вернуть массив, который будет преобразован к JSON позже)
        return $categories;
    }

    public function getSubcategories(): array
    {
        $this->validator->validate($_GET);

        // Получаем подкатегории через сервис
        $subcategories = $this->categoryService->getSubcategories($_GET['category_id']);

        // Возвращаем данные
        return $subcategories;
    }
}
