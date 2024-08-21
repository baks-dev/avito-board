<?php

namespace BaksDev\Avito\Board\Repository\AllProducts;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AllProductsInterface
{
    /** Метод получает массив элементов продукции с соотношением свойств */
    public function findAll(UserProfileUid $profile): array|bool;
}