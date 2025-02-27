<?php

namespace App\Controllers;

use App\Services\FavoriteService;
use App\Validators\FavoriteValidator;
use App\Middleware\AuthMiddleware;

class FavoriteController
{
    protected FavoriteValidator $validator;

    public function __construct()
    {
        $this->validator = new FavoriteValidator();
    }

    /**
     * Добавление товара в избранное.
     * Ожидаемые POST-параметры: product_id.
     *
     * @return array
     */
    public function addFavorite(): array
    {
        $data = $_POST;
        $this->validator->validateAdd($data);

        $userId = (new AuthMiddleware())->handle();
        $favoriteService = new FavoriteService($userId);
        $favoriteService->addFavorite((int)$data['product_id']);
        $favorites = $favoriteService->getFavorites();

        return $favorites;
    }

    /**
     * Удаление товара из избранного.
     * Ожидаемые POST-параметры: product_id.
     *
     * @return array
     */
    public function removeFavorite(): array
    {
        $data = $_POST;
        $this->validator->validateRemove($data);

        $userId = (new AuthMiddleware())->handle();
        $favoriteService = new FavoriteService($userId);
        $favoriteService->removeFavorite((int)$data['product_id']);
        $favorites = $favoriteService->getFavorites();

        return $favorites;
    }

    /**
     * Очистка избранного.
     *
     * @return array
     */
    public function clearFavorites(): array
    {
        // При необходимости можно добавить дополнительную валидацию входящих данных
        $data = $_POST;
        $this->validator->validateClear($data);

        $userId = (new AuthMiddleware())->handle();
        $favoriteService = new FavoriteService($userId);
        $favoriteService->clearFavorites();

        return ['message' => 'Избранное очищено'];
    }

    /**
     * Получение списка избранных товаров.
     *
     * @return array
     */
    public function getFavorites(): array
    {
        $userId = (new AuthMiddleware())->handle();
        $favoriteService = new FavoriteService($userId);
        $favorites = $favoriteService->getFavorites();

        return $favorites;
    }
}