<?php

namespace App\Controllers;

abstract class AbstractController
{
    protected function getPostData(): array
    {
        return $_POST;
    }

    protected function getGetData(): array
    {
        return $_GET;
    }

    /**
     * Валидация входных данных
     * 
     * @param array $data Данные для валидации
     * @param object $validator Валидатор
     * @return bool
     */
    protected function validate(array $data, $validator): bool
    {
        return $validator->validate($data);
    }
}
