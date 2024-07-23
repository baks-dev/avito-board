<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Doctrine;

use BaksDev\Core\Type\UidType\Uid;

final class AvitoBoardUid extends Uid
{
    /** Тестовый идентификатор */
    public const string TEST = '3beab64b-fb0f-4cd0-a5ab-e619b29ac02d';

    public const string TYPE = 'avito_board';
}
