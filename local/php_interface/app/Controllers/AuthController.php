<?php

namespace App\Controllers;

use App\Validators\RegistrationValidator;
use App\Validators\LoginValidator;
use App\Services\AuthService;

class AuthController
{
    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Регистрация пользователя по ФИО, E-mail, (необязательный) телефону, паролю и подтверждению пароля.
     * При успешной регистрации генерируется токен и сохраняется в пользовательском поле UF_API_TOKEN.
     */
    public function register(): array
    {
        // Получаем входящие данные (например, из $_POST)
        $data = $_POST;

        // Валидируем входные данные
        $validator = new RegistrationValidator();
        $validator->validate($data);

        $token = $this->authService->registerUser($data);

        return ['token'   => $token];
    }

    /**
     * Авторизация пользователя по E-mail и паролю.
     * Если авторизация проходит успешно, возвращается токен (из базы или сгенерированный новый).
     * Токен используется для идентификации пользователя в дальнейшем.
     */
    public function login(): array
    {
        // Получаем входящие данные (например, из $_POST)
        $data = $_POST;

        // Валидируем входные данные
        $validator = new LoginValidator();
        $validator->validate($data);

        $token = $this->authService->loginUser($data);

        return ['token'   => $token];
    }
}
