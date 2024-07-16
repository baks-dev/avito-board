<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Main;

use BaksDev\Core\Type\UidType\UidType;

final class AvitoProductType extends UidType
{
    public function getClassType(): string
    {
        return AvitoProductUid::class;
    }

    public function getName(): string
    {
        return AvitoProductUid::TYPE;
    }
}
