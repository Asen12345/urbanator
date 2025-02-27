<?php

namespace App\Services;

use App\Services\ProductService;

class FavoriteFormatterService
{
    /**
     * Форматирует данные товара для отображения в избранном.
     * Выводит: изображение (PREVIEW_PICTURE или DETAIL_PICTURE), название,
     * минимальную цену из торговых предложений и суммарное количество.
     *
     * @param int $productId
     * @return array
     * @throws \Exception Если товар не найден
     */
    public static function formatProduct(int $productId): array
    {
        $productService = new ProductService();
        $product = $productService->getProduct($productId);

        // Определяем изображение: используем PREVIEW_PICTURE, если присутствует, иначе DETAIL_PICTURE
        $image = $product['PREVIEW_PICTURE'] ?? $product['DETAIL_PICTURE'];
        $name = $product['NAME'] ?? '';

        // Анализируем торговые предложения для вычисления минимальной цены и суммарного количества
        $minPrice = null;
        $totalQuantity = 0;
        if (!empty($product['TRADE_OFFERS']) && is_array($product['TRADE_OFFERS'])) {
            foreach ($product['TRADE_OFFERS'] as $offer) {
                $offerPrice = $offer['PRICE']['PRICE'] ?? 0;
                if ($minPrice === null || $offerPrice < $minPrice) {
                    $minPrice = $offerPrice;
                }
                $totalQuantity += (int)$offer['QUANTITY'];
            }
        }
        // Если торговых предложений нет, устанавливаем минимальную цену в 0
        $minPrice = $minPrice ?? 0;

        return [
            'id'             => $productId,
            'name'           => $name,
            'image'          => $image,
            'min_price'      => $minPrice,
            'total_quantity' => $totalQuantity,
        ];
    }
}