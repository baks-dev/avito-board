<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Doctrine\Event;

use BaksDev\Core\Type\UidType\UidType;

final class AvitoBoardEventType extends UidType
{
    public function getClassType(): string
    {
        return AvitoBoardEventUid::class;
    }

    public function getName(): string
    {
        return AvitoBoardEventUid::TYPE;
    }
}