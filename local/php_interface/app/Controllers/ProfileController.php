<?php

namespace App\Controllers;

use App\Middleware\AuthMiddleware;
use App\Services\ProfileService;
use App\Validators\ProfileValidator;
use App\Interfaces\ProfileInterface;

class ProfileController extends AbstractAuthenticatedController
{
    private ProfileInterface $profileService;
    private ProfileValidator $validator;

    /**
     * Конструктор контроллера профиля.
     */
    public function __construct()
    {
        parent::__construct(new AuthMiddleware());

        $this->profileService = new ProfileService();
        $this->validator = new ProfileValidator();
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
        $data = $this->getPostData();
        $this->validate($data, $this->validator);

        $updateResult = $this->profileService->updateProfile($data, $this->userId);

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
        $data = $this->getPostData();
        $this->validator->validatePassword($data);

        $changeResult = $this->profileService->changePassword($data, $this->userId);

        return [
            'success' => $changeResult,
            'message' => $changeResult ? 'Пароль успешно изменен' : 'Ошибка при изменении пароля'
        ];
    }
}
