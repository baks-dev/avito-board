<?php

namespace BaksDev\Avito\Board\Repository\AllProductsWithMapper;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AllProductsWithMapperInterface
{
    /** Метод получает массив элементов продукции с соотношением свойств */
    public function findAll(UserProfileUid $profile): array|bool;
}