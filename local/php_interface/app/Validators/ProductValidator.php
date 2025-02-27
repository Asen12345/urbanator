<?php

namespace App\Validators;

use Rakit\Validation\Validator;

class ProductValidator
{
    public function validate(array $data): bool
    {
        $validator = new Validator();

        $validation = $validator->make($data, [
            'id' => 'required|numeric|min:1'
        ]);

        $validation->validate();

        if ($validation->fails()) {
            $errors = $validation->errors();
            throw new \Exception("Валидация не прошла: " . json_encode($errors->toArray()), 400);
        }

        return true;
    }
}