<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Doctrine\Event;

use BaksDev\Core\Type\UidType\Uid;

final class AvitoBoardEventUid extends Uid
{
    /** Тестовый идентификатор */
    public const string TEST = 'eb99bb14-ebf2-4d5d-8698-51c511768b43';

    public const string TYPE = 'avito_board_event';
}