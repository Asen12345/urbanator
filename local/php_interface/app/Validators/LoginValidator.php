<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class LoginValidator
{
    /**
     * Валидация данных авторизации.
     * Обязательные поля: email и password.
     */
    public function validate(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}