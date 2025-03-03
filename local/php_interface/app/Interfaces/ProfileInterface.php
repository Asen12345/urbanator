<?php

namespace App\Interfaces;

interface ProfileInterface
{
    /**
     * Обновление профиля пользователя.
     *
     * @param array $userData Данные пользователя для обновления
     * @param int $userId ID пользователя
     * @return bool Результат обновления
     */
    public function updateProfile(array $userData, int $userId): bool;
    
    /**
     * Получение данных профиля пользователя.
     *
     * @param int $userId ID пользователя
     * @return array Данные профиля
     */
    public function getProfile(int $userId): array;
}