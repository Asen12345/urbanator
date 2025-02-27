<?php

namespace App\Interfaces;

use App\Enums\Basket\UpdateFields;

interface BasketInterface
{
    /**
     * Добавление товара в корзину.
     *
     * @param int $tradeOfferId ID торгового предложения
     *
     * @return void
     */
    public function addProduct(int $tradeOfferId): void;

    /**
     * Удаление товара из корзины.
     *
     * @param int $tradeOfferId ID торгового предложения
     *
     * @return void
     */
    public function removeProduct(int $tradeOfferId): void;

    /**
     * Обновление количества товара в корзине.
     *
     * @param UpdateFields $type тип операции (PLUS/MINUS)
     * @param int $tradeOfferId ID торгового предложения
     *
     * @return void
     */
    public function updateQuantity(UpdateFields $type, int $tradeOfferId): void;

    /**
     * Очистка корзины.
     *
     * @return void
     */
    public function clearBasket(): void;

    /**
     * Получение содержимого корзины.
     *
     * @return array
     */
    public function getBasket(): array;
}
