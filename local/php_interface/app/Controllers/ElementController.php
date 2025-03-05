<?php

namespace App\Controllers;

use App\Services\ElementService;
use App\Validators\ElementValidator;
use App\Interfaces\ElementInterface;

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
        $validatedData = $this->validate($data, $this->validator);
        
        // Используем инфоблок с ID 38 согласно требованию
        $iblockId = 38;
        $code = $validatedData['code'];
        
        $element = $this->elementService->getElementByCode($iblockId, $code);
        
        if ($element === null) {
            throw new \Exception('Элемент не найден', 404);
        }
        
        return $element;
    }
} 