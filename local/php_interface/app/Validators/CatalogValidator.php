<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class CatalogValidator
{
    /**
     * Валидирует входящие данные для каталога.
     * Ожидаемые поля:
     * - category_id (optional, numeric)
     * - price_min (optional, numeric)
     * - price_max (optional, numeric)
     * - query (optional, string)
     * - page (optional, numeric, минимум 1)
     * - limit (optional, numeric, минимум 1)
     * - properties (optional, array)
     * - in_stock (optional, boolean)
     *
     * @param array $data
     * @return array Отфильтрованные и преобразованные данные
     */
    public function validate(array $data): array
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'category_id' => 'numeric|min:1',
            'price_min'   => 'numeric',
            'price_max'   => 'numeric',
            'page'        => 'numeric|min:1',
            'limit'       => 'numeric|min:1',
            'properties'  => 'array',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Ошибки валидации: " . json_encode($errors->toArray()), 400);
        }

        // Устанавливаем значения по умолчанию
        $data['page']  = isset($data['page']) ? (int)$data['page'] : 1;
        $data['limit'] = isset($data['limit']) ? (int)$data['limit'] : 10;

        return $data;
    }
}