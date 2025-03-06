<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class ElementValidator
{
    /**
     * Валидирует входные данные для поиска элемента по коду
     *
     * @param array $data Входные данные
     * @return bool 
     * @throws \Exception Если данные не прошли валидацию
     */
    public function validate(array $data): bool
    {
        $validator = new Validator();

        // Определяем правила валидации (параметры не обязательны, но если переданы — должны быть целыми)
        $validation = $validator->make($data, [
            'code'  => 'required'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }

    /**
     * Валидирует входные данные для получения элементов из инфоблока
     *
     * @param array $data Входные данные
     * @return bool 
     * @throws \Exception Если данные не прошли валидацию
     */
    public function validateGetAllElements(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'iblock_id' => 'required|numeric'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }

    /**
     * Валидирует входные данные для получения товаров из свойства FILTER_NEW
     *
     * @param array $data Входные данные
     * @return bool 
     * @throws \Exception Если данные не прошли валидацию
     */
    public function validateGetProductsFromFilterNew(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'element_id' => 'required|numeric',
            'page' => 'numeric'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}
