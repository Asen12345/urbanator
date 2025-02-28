<?php

namespace App\Controllers;

use App\Services\FaqService;
use App\Validators\FaqValidator;

class FaqController
{  
    protected FaqService $faqService;
    protected FaqValidator $validator;

    public function __construct()
    {
        $this->faqService = new FaqService();
        $this->validator = new FaqValidator();
    }

    /**
     * Получение списка вопросов и ответов из указанного инфоблока.
     *
     * Ожидается, что входные параметры передаются через POST:
     * iblock_id - ID инфоблока с вопросами и ответами
     *
     * @return array
     */
    public function getFaq(): array
    {
        // Получаем данные из POST
        $data = $_GET;

        // Валидируем входные параметры
        $validatedData = $this->validator->validate($data);

        // Получаем список вопросов и ответов
        $result = $this->faqService->getFaqItems($validatedData['iblock_id']);

        return $result;
    }
}
