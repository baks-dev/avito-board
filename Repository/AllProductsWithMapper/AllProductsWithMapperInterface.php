<?php

namespace BaksDev\Avito\Board\Repository\AllProductsWithMapper;

use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

interface AllProductsWithMapperInterface
{
    public function profile(UserProfile|UserProfileUid|string $profile): self;

    /** Метод получает массив элементов продукции с соотношением свойств */
    public function execute(): array|false;
}
