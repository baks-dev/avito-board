<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type;

use BaksDev\Core\Type\UidType\UidType;

final class AvitoBoardType extends UidType
{
    public function getClassType(): string
    {
        return AvitoBoardUid::class;
    }

    public function getName(): string
    {
        return AvitoBoardUid::TYPE;
    }
}