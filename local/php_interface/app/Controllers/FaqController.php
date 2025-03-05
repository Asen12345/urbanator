<?php

namespace App\Controllers;

use App\Services\FaqService;
use App\Validators\FaqValidator;
use App\Interfaces\FaqInterface;

class FaqController extends AbstractController
{
    protected FaqInterface $faqService;
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
        $data = $this->getGetData();

        $this->validate($data, $this->validator);

        // Получаем список вопросов и ответов
        $result = $this->faqService->getFaqItems($data['iblock_id']);

        return $result;
    }
}
