<?php

namespace App\Interfaces;

interface FaqInterface
{
    /**
     * Получает список вопросов и ответов из указанного инфоблока.
     * 
     * На выходе возвращается массив с элементами, содержащими:
     * - id: ID элемента
     * - name: Название элемента (вопрос)
     * - preview_text: Текст анонса (ответ)
     * 
     * @param int $iblockId ID инфоблока с вопросами и ответами
     * @return array
     */
    public function getFaqItems(int $iblockId): array;
}