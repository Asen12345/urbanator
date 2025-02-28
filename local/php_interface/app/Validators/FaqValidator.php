<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class FaqValidator
{
    /**
     * Валидирует входящие данные для получения FAQ.
     * Ожидаемые поля:
     * - iblock_id (required, numeric, минимум 1)
     *
     * @param array $data
     * @return array Отфильтрованные и преобразованные данные
     */
    public function validate(array $data): array
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'iblock_id' => 'required|numeric|min:1',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Ошибки валидации: " . json_encode($errors->toArray()), 400);
        }

        // Преобразуем iblock_id в целое число
        $data['iblock_id'] = (int)$data['iblock_id'];

        return $data;
    }
}