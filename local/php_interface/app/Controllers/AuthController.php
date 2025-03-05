<?php

namespace App\Controllers;

use App\Validators\RegistrationValidator;
use App\Interfaces\AuthInterface;
use App\Validators\LoginValidator;
use App\Services\AuthService;

class AuthController extends AbstractController
{
    protected AuthInterface $authService;

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
        $data = $this->getPostData();

        // Валидируем входные данные
        $validator = new RegistrationValidator();
        $this->validate($data, $validator);

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
        $data = $this->getPostData();

        // Валидируем входные данные
        $validator = new LoginValidator();
        $this->validate($data, $validator);

        $token = $this->authService->loginUser($data);

        return ['token'   => $token];
    }
}
