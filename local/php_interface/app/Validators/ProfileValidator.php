<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class ProfileValidator
{
    /**
     * Валидация данных профиля пользователя.
     *
     * @param array $data Данные для валидации
     * @return bool Результат валидации
     */
    public function validate(array $data): bool
    {
        $validator = new Validator;

        $validation = $validator->make($data, [
            'name' => 'required',
            'last_name' => 'required',
            'second_name' => 'required',
            'email' => 'required|email',
            'phone' => 'required|regex:/^[0-9+\-\(\)\s]+$/',
        ]);

        $validation->setMessages([
            'name:required' => 'Имя обязательно для заполнения',
            'last_name:required' => 'Фамилия обязательна для заполнения',
            'second_name:required' => 'Отчество обязательно для заполнения',
            'email:required' => 'Email обязателен для заполнения',
            'email:email' => 'Некорректный формат email',
            'phone:required' => 'Номер телефона обязателен для заполнения',
            'phone:regex' => 'Некорректный формат номера телефона',
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Ошибки валидации: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}
