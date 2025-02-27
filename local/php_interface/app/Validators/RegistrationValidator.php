<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class RegistrationValidator
{
    /**
     * Валидация данных регистрации.
     * Обязательные поля: fio, email, password, password_confirmation (и password_confirmation должна совпадать с password).
     */
    public function validate(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'fio'                   => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:6',
            'password_confirmation' => 'required|same:password',
            // Поле phone является необязательным
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}