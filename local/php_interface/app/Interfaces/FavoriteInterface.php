<?php

namespace App\Interfaces;

interface FavoriteInterface
{
    /**
     * Добавление товара в избранное.
     *
     * @param int $productId ID товара
     * @return void
     */
    public function addFavorite(int $productId): void;

    /**
     * Удаление товара из избранного.
     *
     * @param int $productId ID товара
     * @return void
     */
    public function removeFavorite(int $productId): void;

    /**
     * Очистка избранного.
     *
     * @return void
     */
    public function clearFavorites(): void;

    /**
     * Получение списка избранных товаров с форматированными данными.
     *
     * @return array
     */
    public function getFavorites(): array;
}