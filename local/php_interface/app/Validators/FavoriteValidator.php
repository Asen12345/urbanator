<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class FavoriteValidator
{
    /**
     * Валидирует данные для добавления товара в избранное.
     * Ожидается наличие параметра product_id (числовое, минимум 1).
     *
     * @param array $data
     * @return bool
     * @throws \Exception В случае ошибки валидации.
     */
    public function validateAdd(array $data): bool
    {
        $validator = new Validator();
        $validation = $validator->make($data, [
            'product_id' => 'required|numeric|min:1',
        ]);
        $validation->validate();
        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }
        return true;
    }

    /**
     * Валидирует данные для удаления товара из избранного.
     * Ожидается наличие параметра product_id (числовое, минимум 1).
     *
     * @param array $data
     * @return bool
     * @throws \Exception В случае ошибки валидации.
     */
    public function validateRemove(array $data): bool
    {
        $validator = new Validator();
        $validation = $validator->make($data, [
            'product_id' => 'required|numeric|min:1',
        ]);
        $validation->validate();
        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }
        return true;
    }

    /**
     * Валидирует данные для очистки избранного.
     * Дополнительных параметров не требуется.
     *
     * @param array $data
     * @return bool
     */
    public function validateClear(array $data): bool
    {
        return true;
    }
}