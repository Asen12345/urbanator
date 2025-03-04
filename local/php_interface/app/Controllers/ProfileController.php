<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\ProfileService;
use App\Validators\ProfileValidator;

class ProfileController
{
    private ProfileService $profileService;
    private ProfileValidator $validator;
    private int $userId;

    /**
     * Конструктор контроллера профиля.
     */
    public function __construct()
    {
        $authMiddleware = new AuthMiddleware();

        $this->profileService = new ProfileService();
        $this->validator = new ProfileValidator();
        $this->userId = $authMiddleware->handle();
    }

    /**
     * Получение данных профиля пользователя.
     *
     * @return array
     */
    public function getProfile(): array
    {
        $profileData = $this->profileService->getProfile($this->userId);

        return $profileData;
    }

    /**
     * Обновление профиля пользователя.
     *
     * @return array
     */
    public function updateProfile(): array
    {
        $this->validator->validate($_POST);

        $updateResult = $this->profileService->updateProfile($_POST, $this->userId);

        return [
            'success' => $updateResult,
            'message' => $updateResult ? 'Профиль успешно обновлен' : 'Ошибка при обновлении профиля'
        ];
    }

    /**
     * Изменение пароля пользователя.
     *
     * @return array
     */
    public function changePassword(): array
    {
        $this->validator->validatePassword($_POST);

        $changeResult = $this->profileService->changePassword($_POST, $this->userId);

        return [
            'success' => $changeResult,
            'message' => $changeResult ? 'Пароль успешно изменен' : 'Ошибка при изменении пароля'
        ];
    }
}
