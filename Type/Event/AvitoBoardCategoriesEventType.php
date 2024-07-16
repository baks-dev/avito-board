<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Event;

use BaksDev\Core\Type\UidType\UidType;

final class AvitoBoardCategoriesEventType extends UidType
{
    public function getClassType(): string
    {
        return AvitoBoardCategoriesEventUid::class;
    }

    public function getName(): string
    {
        return AvitoBoardCategoriesEventUid::TYPE;
    }
}
