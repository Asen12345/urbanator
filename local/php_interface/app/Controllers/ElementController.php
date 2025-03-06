<?php

namespace App\Controllers;

use App\Services\ElementService;
use App\Validators\ElementValidator;
use App\Interfaces\ElementInterface;
use App\Enums\Element\IblockTypes;

class ElementController extends AbstractController
{
    protected ElementInterface $elementService;
    protected ElementValidator $validator;

    public function __construct()
    {
        $this->elementService = new ElementService();
        $this->validator = new ElementValidator();
    }

    /**
     * Получает элемент инфоблока по коду
     * 
     * @return array
     */
    public function getByCode(): array
    {
        $data = $this->getGetData();

        // Валидируем входные данные
        $this->validate($data, $this->validator);

        // Используем инфоблок с ID 38 согласно требованию
        $iblockId = 38;
        $code = $data['code'];

        $element = $this->elementService->getElementByCode($iblockId, $code);

        if ($element === null) {
            throw new \Exception('Элемент не найден', 404);
        }

        return $element;
    }

    /**
     * Получает все элементы из инфоблока 16
     * 
     * @return array
     */
    public function getAllElementsFromSpecialIblock(): array
    {
        $elements = $this->elementService->getAllElementsFromIblock(IblockTypes::SPECIAL_IBLOCK);

        return [
            'elements' => $elements,
            'count' => count($elements)
        ];
    }

    /**
     * Получает форматированные товары из свойства FILTER_NEW элемента
     * 
     * @return array
     */
    public function getFormattedProductsFromFilterNew(): array
    {
        $data = $this->getGetData();

        // Валидируем входные данные
        $this->validator->validateGetProductsFromFilterNew($data);

        $elementId = (int)$data['element_id'];
        $page = isset($data['page']) ? (int)$data['page'] : 1;
        $perPage = 20; // Количество элементов на странице

        $result = $this->elementService->getFormattedProductsFromFilterNew($elementId, $page, $perPage);

        return [
            'products' => $result['products'],
            'count' => count($result['products']),
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => $result['total_pages'],
            'total_products' => $result['total_products']
        ];
    }
}
