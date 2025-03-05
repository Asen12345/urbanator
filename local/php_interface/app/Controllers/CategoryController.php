<?php

namespace App\Controllers;

use App\Interfaces\CategoryInterface;
use App\Services\CategoryService;
use App\Validators\CategoryValidator;

class CategoryController extends AbstractController
{
    protected CategoryInterface $categoryService;
    protected CategoryValidator $validator;

    public function __construct()
    {
        $this->categoryService =  new CategoryService();
        $this->validator     = new CategoryValidator();
    }

    public function getList(): array
    {
        $categories = $this->categoryService->getCategories();

        // Возвращаем данные (контроллер может вернуть массив, который будет преобразован к JSON позже)
        return $categories;
    }

    public function getSubcategories(): array
    {
        $data = $this->getGetData();
        $this->validate($data, $this->validator);

        // Получаем подкатегории через сервис
        $subcategories = $this->categoryService->getSubcategories($data['category_id']);

        // Возвращаем данные
        return $subcategories;
    }
}
