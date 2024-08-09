<?php

namespace BaksDev\Avito\Board\Repository\Feed\AllProducts;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AllProductsWithMappingInterface
{
    /** Метод получает массив элементов продукции с соотношением свойств */
    public function findAll(UserProfileUid $profile = null): array|bool;
}