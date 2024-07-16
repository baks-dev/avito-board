<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Main;

use BaksDev\Core\Type\UidType\Uid;

final class AvitoProductUid extends Uid
{
    /** Тестовый идентификатор */
    public const string TEST = 'd8922dec-89c1-47ec-af0c-18eede123f46';

    public const string TYPE = 'avito_board';
}
