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
} 