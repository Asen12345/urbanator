<?php

namespace App\Services;

use App\Interfaces\AuthInterface;

class AuthService implements AuthInterface
{
    public function loginUser(array $data): string
    {
        $email    = $data['email'];
        $password = $data['password'];

        // Используем глобальную переменную $USER для авторизации в Bitrix
        global $USER;

        // Если пользователь уже авторизован, разлогиниваем его
        if ($USER->IsAuthorized()) {
            $USER->Logout();
        }

        // Пытаемся выполнить авторизацию (LOGIN сам обновляет сессию)
        $result = $USER->Login($email, $password, "Y");
        if ($result !== true) {
            throw new \Exception("Неверный email или пароль", 401);
        }

        $userId = $USER->GetID();
        $userData = \CUser::GetByID($userId)->Fetch();

        // Если токен не установлен, генерируем новый и сохраняем его
        if (empty($userData['UF_API_TOKEN'])) {
            $token = bin2hex(random_bytes(32));
            $user = new \CUser;
            $user->Update($userId, ["UF_API_TOKEN" => $token]);
        } else {
            $token = $userData['UF_API_TOKEN'];
        }

        return $token;
    }

    public function registerUser(array $data): string
    {
        // Подготавливаем поля для создания пользователя.
        // Для Bitrix обязательным является наличие LOGIN, поэтому в качестве логина используем email.
        $userFields = [
            "NAME"              => $data['fio'],
            "EMAIL"             => $data['email'],
            "LOGIN"             => $data['email'],
            "PASSWORD"          => $data['password'],
            "CONFIRM_PASSWORD"  => $data['password_confirmation'],
            "ACTIVE"            => "Y",
        ];

        if (isset($data['phone'])) {
            $userFields["PERSONAL_PHONE"] = $data['phone'];
        }

        $user = new \CUser;
        $userId = $user->Add($userFields);

        if (!$userId) {
            // Если произошла ошибка при создании пользователя, выбрасываем исключение
            throw new \Exception("Ошибка регистрации: " . $user->LAST_ERROR, 400);
        }

        // Генерируем токен (например, используя случайные байты)
        $token = bin2hex(random_bytes(32));

        // Обновляем пользователя, сохраняя токен в пользовательском поле UF_API_TOKEN
        $user->Update($userId, ["UF_API_TOKEN" => $token]);

        return $token;
    }
}
