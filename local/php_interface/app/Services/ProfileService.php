<?php

namespace App\Services;

use App\Interfaces\ProfileInterface;
use App\Enums\Profile\ProfileFields;
use Bitrix\Main\UserTable;

class ProfileService implements ProfileInterface
{
    /**
     * Обновление профиля пользователя.
     *
     * @param array $userData Данные пользователя для обновления
     * @param int $userId ID пользователя
     * @return bool Результат обновления
     */
    public function updateProfile(array $userData, int $userId): bool
    {

        $fields = [
            ProfileFields::NAME->value => $userData['name'],
            ProfileFields::LAST_NAME->value => $userData['last_name'],
            ProfileFields::SECOND_NAME->value => $userData['second_name'],
            ProfileFields::EMAIL->value => $userData['email'],
            ProfileFields::PHONE->value => $userData['phone'],
        ];

        $user = new \CUser;
        $result = $user->Update($userId, $fields);

        if (!$result) {
            throw new \Exception($user->LAST_ERROR);
        }

        return true;
    }

    /**
     * Получение данных профиля пользователя.
     *
     * @param int $userId ID пользователя
     * @return array Данные профиля
     */
    public function getProfile(int $userId): array
    {
        $userData = UserTable::getList([
            'select' => [
                ProfileFields::NAME->value,
                ProfileFields::LAST_NAME->value,
                ProfileFields::SECOND_NAME->value,
                ProfileFields::EMAIL->value,
                ProfileFields::PHONE->value,
            ],
            'filter' => ['ID' => $userId]
        ])->fetch();

        if (!$userData) {
            return [];
        }

        return [
            'name' => $userData[ProfileFields::NAME->value],
            'last_name' => $userData[ProfileFields::LAST_NAME->value],
            'second_name' => $userData[ProfileFields::SECOND_NAME->value],
            'email' => $userData[ProfileFields::EMAIL->value],
            'phone' => $userData[ProfileFields::PHONE->value],
        ];
    }
}
