<?php

namespace App\Controllers;

use App\Services\BasketService;
use App\Validators\BasketValidator;
use App\Middleware\AuthMiddleware;
use App\Enums\Basket\UpdateFields;
use App\Interfaces\BasketInterface;

class BasketController extends AbstractAuthenticatedController
{
    protected BasketValidator $validator;

    public function __construct()
    {
        parent::__construct(new AuthMiddleware());
        $this->validator = new BasketValidator();
    }

    /**
     * Получает экземпляр сервиса корзины для авторизованного пользователя.
     *
     * @return BasketInterface
     */
    private function getBasketService(): BasketInterface
    {
        return new BasketService($this->userId);
    }

    /**
     * Добавление товара в корзину.
     * Ожидаемые POST-параметры: trade_offer_id.
     */
    public function addItem(): array
    {
        $data = $this->getPostData();
        $this->validator->validateAdd($data);

        $tradeOfferId    = (int)$data['trade_offer_id'];

        $basketService = $this->getBasketService();
        $basketService->addProduct($tradeOfferId);

        return [
            'message' => 'Товар успешно добавлен в корзину',
            'basket'  => $basketService->getBasket(),
        ];
    }

    /**
     * Обновление количества товара в корзине.
     * Ожидаемые POST-параметры: type, trade_offer_id.
     * Если quantity <= 0, товар удаляется.
     */
    public function updateItem(): array
    {
        $data = $this->getPostData();
        $this->validator->validateUpdate($data);

        $tradeOfferId   = (int)$data['trade_offer_id'];
        $type = UpdateFields::from($data['type']);

        $basketService = $this->getBasketService();
        $basketService->updateQuantity($type, $tradeOfferId);

        return [
            'message' => 'Количество товара обновлено',
            'basket'  => $basketService->getBasket(),
        ];
    }

    /**
     * Удаление товара из корзины.
     * Ожидаемые POST-параметры: trade_offer_id.
     */
    public function removeItem(): array
    {
        $data = $this->getPostData();
        $this->validator->validateRemove($data);

        $tradeOfferId   = (int)$data['trade_offer_id'];

        $basketService = $this->getBasketService();
        $basketService->removeProduct($tradeOfferId);

        return [
            'message' => 'Товар удалён из корзины',
            'basket'  => $basketService->getBasket(),
        ];
    }

    /**
     * Очистка корзины.
     */
    public function clearBasket(): array
    {
        $basketService = $this->getBasketService();
        $basketService->clearBasket();

        return [
            'message' => 'Корзина очищена'
        ];
    }

    /**
     * Получение текущего содержимого корзины.
     */
    public function getBasket(): array
    {
        $basketService = $this->getBasketService();

        return $basketService->getBasket();
    }
}
