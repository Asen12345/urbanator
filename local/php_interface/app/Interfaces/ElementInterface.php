<?php

namespace App\Interfaces;

interface ElementInterface
{
    /**
     * Поиск элемента в инфоблоке по коду
     *
     * @param int $iblockId ID инфоблока
     * @param string $code Код элемента
     * @return array|null Массив с данными элемента или null, если элемент не найден
     */
    public function getElementByCode(int $iblockId, string $code): ?array;

    /**
     * Получение всех элементов из инфоблока с указанным ID
     * 
     * @param int $iblockId ID инфоблока
     * @return array Массив с элементами инфоблока
     */
    public function getAllElementsFromIblock(int $iblockId): array;

    /**
     * Получение товаров из свойства FILTER_NEW элемента
     *
     * @param int $elementId ID элемента
     * @return array Массив с ID товаров
     */
    public function getFormattedProductsFromFilterNew(int $elementId, int $page, int $perPage): array;
}
