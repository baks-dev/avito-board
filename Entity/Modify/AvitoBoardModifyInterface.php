<?php

namespace Entity\Modify;

namespace BaksDev\Avito\Board\Entity\Modify;

use BaksDev\Core\Type\Modify\ModifyAction;

interface AvitoBoardModifyInterface
{
    public function getAction(): ModifyAction;
}
