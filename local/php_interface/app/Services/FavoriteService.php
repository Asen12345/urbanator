<?php

namespace App\Services;

use App\Interfaces\FavoriteInterface;

class FavoriteService implements FavoriteInterface
{
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Получает список избранных товаров пользователя из пользовательского поля UF_FAVORITES.
     *
     * @return array
     */
    private function getUserFavorites(): array
    {
        $userData = \CUser::GetByID($this->userId)->Fetch();
        if (isset($userData['UF_FAVORITES']) && !empty($userData['UF_FAVORITES'])) {
            $favorites = json_decode($userData['UF_FAVORITES'], true);
            return is_array($favorites) ? $favorites : [];
        }
        return [];
    }

    /**
     * Обновляет список избранных товаров в профиле пользователя.
     *
     * @param array $favorites
     * @return void
     */
    private function updateUserFavorites(array $favorites): void
    {
        $user = new \CUser;
        $favoritesJson = json_encode(array_values($favorites));
        $user->Update($this->userId, ["UF_FAVORITES" => $favoritesJson]);
    }

    public function addFavorite(int $productId): void
    {
        $favorites = $this->getUserFavorites();
        if (!in_array($productId, $favorites, true)) {
            $favorites[] = $productId;
            $this->updateUserFavorites($favorites);
        }
    }

    public function removeFavorite(int $productId): void
    {
        $favorites = $this->getUserFavorites();
        if (($key = array_search($productId, $favorites, true)) !== false) {
            unset($favorites[$key]);
            $this->updateUserFavorites($favorites);
        }
    }

    public function clearFavorites(): void
    {
        $this->updateUserFavorites([]);
    }

    public function getFavorites(): array
    {
        $favorites = $this->getUserFavorites();
        $formattedFavorites = [];
        // Используем сервис форматирования для приведения данных к требуемому формату
        $productFormatter = new ProductFormatterService();
        foreach ($favorites as $productId) {
            try {
                $formattedFavorites[] = $productFormatter($productId);
            } catch (\Exception $e) {
                // Если товар не найден или произошла ошибка – пропускаем его
                continue;
            }
        }
        return $formattedFavorites;
    }
}
