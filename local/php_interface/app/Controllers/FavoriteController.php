<?php

namespace App\Controllers;

use App\Services\FavoriteService;
use App\Validators\FavoriteValidator;
use App\Middleware\AuthMiddleware;
use App\Interfaces\FavoriteInterface;

class FavoriteController extends AbstractAuthenticatedController
{
    protected FavoriteValidator $validator;
    protected FavoriteInterface $favoriteService;

    public function __construct()
    {
        parent::__construct(new AuthMiddleware());

        $this->validator = new FavoriteValidator();
        $this->favoriteService = new FavoriteService($this->userId);
    }

    /**
     * Добавление товара в избранное.
     * Ожидаемые POST-параметры: product_id.
     *
     * @return array
     */
    public function addFavorite(): array
    {
        $data = $this->getPostData();
        $this->validator->validateAdd($data);

        $this->favoriteService->addFavorite((int)$data['product_id']);
        $favorites = $this->favoriteService->getFavorites();

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
        $data = $this->getPostData();
        $this->validator->validateRemove($data);

        $this->favoriteService->removeFavorite((int)$data['product_id']);
        $favorites = $this->favoriteService->getFavorites();

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
        $data = $this->getPostData();
        $this->validator->validateClear($data);

        $this->favoriteService->clearFavorites();

        return ['message' => 'Избранное очищено'];
    }

    /**
     * Получение списка избранных товаров.
     *
     * @return array
     */
    public function getFavorites(): array
    {
        $favorites = $this->favoriteService->getFavorites();

        return $favorites;
    }
}
