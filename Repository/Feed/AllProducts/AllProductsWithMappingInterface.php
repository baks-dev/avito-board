<?php

namespace BaksDev\Avito\Board\Repository\Feed\AllProducts;

interface AllProductsWithMappingInterface
{
    /** Метод получает массив элементов продукции с соотношением свойств */
    public function findAll(): array|bool;
}