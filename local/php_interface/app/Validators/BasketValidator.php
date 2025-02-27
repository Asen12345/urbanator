<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class BasketValidator
{
    /**
     * Валидирует данные для добавления товара в корзину.
     * Ожидаемые поля: trade_offer_id (обязательное).
     */
    public function validateAdd(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'trade_offer_id'      => 'required|numeric|min:1',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }

    /**
     * Валидирует данные для обновления количества товара в корзине.
     * Ожидаемые поля: type (обязательное), trade_offer_id (обязательное).
     */
    public function validateUpdate(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'type'      => 'required',
            'trade_offer_id' => 'required|numeric|min:1'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }

    /**
     * Валидирует данные для удаления товара из корзины.
     * Ожидаемые поля: trade_offer_id (обязательное).
     */
    public function validateRemove(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'trade_offer_id' => 'required|numeric|min:1'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}
