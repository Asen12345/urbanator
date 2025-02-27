<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class CategoryValidator
{
    public function validate(array $data): bool
    {
        $validator = new Validator();

        // Определяем правила валидации (параметры не обязательны, но если переданы — должны быть целыми)
        $validation = $validator->make($data, [
            'page'  => 'numeric|min:1',
            'limit' => 'numeric|min:1',
            'category_id' => 'numeric|min:1'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}