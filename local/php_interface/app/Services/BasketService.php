<?php

namespace App\Services;

use App\Interfaces\BasketInterface;
use Bitrix\Main\Loader;
use Bitrix\Sale\Fuser;
use Bitrix\Sale\Basket\Storage;
use Bitrix\Sale\BasketBase as BitrixBasket;
use App\Enums\Basket\UpdateFields;

class BasketService implements BasketInterface
{
    protected int $userId;

    public function __construct(int $userId)
    {
        $this->userId = $userId;
        if (!Loader::includeModule('sale')) {
            throw new \Exception("Модуль sale не установлен");
        }
    }

    /**
     * Загружает объект корзины для авторизованного пользователя.
     *
     * @return BitrixBasket
     */
    protected function loadBasket(): BitrixBasket
    {
        // Получаем идентификатор покупателя (FUSER_ID) для авторизованного пользователя
        $fUserId = Fuser::getIdByUserId($this->userId);
        $basketStorage = Storage::getInstance($fUserId, 's1');
        $basket = $basketStorage->getBasket();
        return $basket;
    }


    public function addProduct(int $tradeOfferId): void
    {
        $basket = $this->loadBasket();

        // Ищем существующий элемент с данным tradeOfferId
        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getProductId() == $tradeOfferId) {
                return;
            }
        }

        // Если не найден — создаём новый элемент корзины
        $item = $basket->createItem('catalog', $tradeOfferId);
        $item->setFields(array(
            'QUANTITY' => 1,
            'CURRENCY' => 'RUB',
            'LID' => 's1',
            'PRODUCT_PROVIDER_CLASS' => 'CCatalogProductProvider',
        ));

        $basket->save();
    }

    public function removeProduct(int $tradeOfferId): void
    {
        $basket = $this->loadBasket();

        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getProductId() == $tradeOfferId) {
                $basketItem->delete();
            }
        }
        $basket->save();
    }

    public function updateQuantity(UpdateFields $type, int $tradeOfferId): void
    {
        $basket = $this->loadBasket();
        $found = false;

        foreach ($basket->getBasketItems() as $basketItem) {
            if ($basketItem->getProductId() == $tradeOfferId) {
                $currentQuantity = $basketItem->getQuantity(); // получаем текущее количество

                // Изменяем количество в зависимости от типа UpdateFields
                if ($type === UpdateFields::MINUS) {
                    $newQuantity = $currentQuantity - 1;
                } elseif ($type === UpdateFields::PLUS) {
                    $newQuantity = $currentQuantity + 1;
                } else {
                    $newQuantity = $currentQuantity;
                }

                if ($newQuantity <= 0) {
                    $basketItem->delete();
                } else {
                    $basketItem->setField('QUANTITY', $newQuantity);
                }
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new \Exception("Товар с ID {$tradeOfferId} не найден в корзине", 404);
        }

        $basket->save();
    }

    public function clearBasket(): void
    {
        $basket = $this->loadBasket();

        foreach ($basket->getBasketItems() as $basketItem) {
            $basketItem->delete();
        }
        $basket->save();
    }

    /**
     * Возвращает корзину в виде массива.
     *
     * @return array
     */
    public function getBasket(): array
    {
        $basket = $this->loadBasket();
        $items = [];

        foreach ($basket->getBasketItems() as $basketItem) {
            // В корзине хранится ID торгового предложения
            $tradeOfferId = $basketItem->getProductId();

            // Получаем данные торгового предложения: название, IBLOCK_ID и картинку
            $elementRes = \CIBlockElement::GetList(
                [],
                ['ID' => $tradeOfferId],
                false,
                false,
                ['ID', 'NAME', 'DETAIL_PICTURE', 'IBLOCK_ID']
            );
            $element = $elementRes->Fetch();

            if ($element) {
                $title = $element['NAME'];
                // Если существует изображение торгового предложения, используем его
                $image = ($element['DETAIL_PICTURE']) ? \CFile::GetPath($element['DETAIL_PICTURE']) : '';

                // Если изображения торгового предложения нет, попытаемся получить изображение родительского товара
                if (!$image) {
                    $parentProductId = null;

                    // Получаем свойство CML2_LINK, которое привязывает торговое предложение к родительскому товару
                    $offerProps = \CIBlockElement::GetProperty(
                        $element['IBLOCK_ID'],
                        $tradeOfferId,
                        [],
                        ["CODE" => "CML2_LINK"]
                    );
                    if ($offerProp = $offerProps->Fetch()) {
                        $parentProductId = $offerProp['VALUE'];
                    }

                    // Если родительский товар найден, получаем его изображение
                    if ($parentProductId) {
                        $productRes = \CIBlockElement::GetList(
                            [],
                            ['ID' => $parentProductId],
                            false,
                            false,
                            ['ID', 'DETAIL_PICTURE']
                        );
                        if ($productElement = $productRes->Fetch()) {
                            if ($productElement['DETAIL_PICTURE']) {
                                $image = \CFile::GetPath($productElement['DETAIL_PICTURE']);
                            }
                        }
                    }
                }
            } else {
                $title = '';
                $image = '';
            }

            $price = ProductService::getOfferPrice($tradeOfferId);

            // Получаем остаток товара
            $catalogProduct = \CCatalogProduct::GetByID($tradeOfferId);
            $remainQuantity = $catalogProduct ? $catalogProduct['QUANTITY'] : 0;

            $items[] = [
                'quantity' => $basketItem->getQuantity(),
                'title'    => $title,
                'price'    => $price,
                'image'    => $image,
                'remain'   => (int)$remainQuantity, // Остаток товара
            ];
        }

        return $items;
    }
}
