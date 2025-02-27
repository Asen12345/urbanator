<?php

namespace App\Services;

use App\Interfaces\ProductInterface;
use App\Enums\Product\ProductFields;
use App\Services\IblockSelectorService;
use App\Config\ConfigLoader;

class ProductService implements ProductInterface
{
    private ?int $selectedIblockId = null;
    protected array $defaultProperties;

    public function __construct()
    {
        $configLoader = new ConfigLoader();
        $iblockSelector = new IblockSelectorService();
        $this->selectedIblockId = $iblockSelector();
        $this->defaultProperties = $configLoader->get('product_default_properties', []);
    }

    /**
     * Получает товар с дополнительными свойствами.
     *
     * @param int $productId
     * @param array|null $properties Список свойств для фильтрации (например, ['PROPERTY_BRAND', 'PROPERTY_RAZMER', ...])
     * @return array
     * @throws \Exception
     */
    public function getProduct(int $productId, ?array $properties = null): array
    {
        if ($this->selectedIblockId === null) {
            throw new \Exception('Ошибка с id каталога продуктов', 400);
        }

        $arFilter = [
            "IBLOCK_ID" => $this->selectedIblockId,
            "ID"        => $productId,
            "ACTIVE"    => 'Y'
        ];

        $arSelect = [
            'ID',
            ProductFields::NAME->value,
            ProductFields::DETAIL_PICTURE->value,
            ProductFields::PREVIEW_PICTURE->value,
            ProductFields::DETAIL_DESCRIPTION->value,
            ProductFields::MORE_PHOTO->value
        ];

        $rsProduct = \CIBlockElement::GetList([], $arFilter, false, ['nTopCount' => 1], $arSelect);
        $product = $rsProduct->GetNext();

        if (!$product) {
            throw new \Exception("Товар с ID {$productId} не найден", 404);
        }

        // Получаем торговые предложения и уникальные свойства из них.
        $tradeOffersData = $this->getTradeOffers($product['ID']);

        return [
            'ID'                 => $product['ID'],
            'NAME'               => $product['NAME'],
            'DETAIL_PICTURE'     => \CFile::GetPath($product['DETAIL_PICTURE']),
            'PREVIEW_PICTURE'    => \CFile::GetPath($product['PREVIEW_PICTURE']),
            'DETAIL_DESCRIPTION' => $product['DETAIL_TEXT'] ?? null,
            'MORE_PHOTO'         => $this->getMorePhoto($product['ID']),
            'TRADE_OFFERS'       => $tradeOffersData['OFFERS'],
        ];
    }

    /**
     * Метод для получения значений свойства торговых предложений и самих предложений.
     *
     * @param int $productId
     * @return array
     */
    private function getTradeOffers(int $productId): array
    {
        $tradeOffers = [];

        if (!\Bitrix\Main\Loader::includeModule('catalog')) {
            return ['OFFERS' => $tradeOffers];
        }

        $offers = \CCatalogSKU::getOffersList(
            [$productId],
            $this->selectedIblockId,
            ['ACTIVE' => 'Y', 'AVAILABLE' => 'Y'],
            ['ID', 'NAME', 'DETAIL_PICTURE', 'CATALOG_QUANTITY', 'IBLOCK_ID']
        );

        if (!empty($offers[$productId])) {
            foreach ($offers[$productId] as $offer) {
                $price = self::getOfferPrice($offer['ID']);
                $tradeOffers[] = [
                    'ID'             => $offer['ID'],
                    'NAME'           => $offer['NAME'],
                    'DETAIL_PICTURE' => \CFile::GetPath($offer['DETAIL_PICTURE']),
                    'PRICE'          => $price,
                    'QUANTITY'       => $offer['CATALOG_QUANTITY'],
                ];
            }
        }

        return ['OFFERS' => $tradeOffers];
    }

    /**
     * Получает оптимальную цену для торгового предложения.
     *
     * @param int $offerId
     * @return array
     */
    public static function getOfferPrice(int $offerId): array
    {
        $price = 0.0;
        $oldPrice = null;

        $optimalPrice = \CCatalogProduct::GetOptimalPrice(
            $offerId,
            1,
            [],
            'N'
        );

        if ($optimalPrice) {
            $optimalPrice = $optimalPrice['RESULT_PRICE'];
            $price = (float)$optimalPrice['BASE_PRICE'];

            if (isset($optimalPrice['DISCOUNT_PRICE']) && $optimalPrice['DISCOUNT_PRICE'] < $price) {
                $oldPrice = $price;
                $price = (float)$optimalPrice['DISCOUNT_PRICE'];
            }
        }

        return [
            'PRICE'     => $price,
            'OLD_PRICE' => $oldPrice
        ];
    }

    /**
     * Получает изображения для доп. фотографий товара.
     *
     * @param int $productId
     * @return array
     */
    private function getMorePhoto(int $productId): array
    {
        $morePhotos = [];
        $rsProps = \CIBlockElement::GetProperty(
            $this->selectedIblockId,
            $productId,
            [],
            ["CODE" => ProductFields::MORE_PHOTO->value]
        );
        while ($prop = $rsProps->GetNext()) {
            $path = \CFile::GetPath($prop['VALUE']);
            if ($path) {
                $morePhotos[] = $path;
            }
        }
        return $morePhotos;
    }
}
