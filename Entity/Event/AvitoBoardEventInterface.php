<?php

namespace BaksDev\Avito\Board\Entity\Event;

use BaksDev\Avito\Board\Type\Event\AvitoBoardEventUid;

/**
 * Интерфейс, который должны реализовывать DTO, через которые изменяется сущность
 * @see AvitoBoardEvent
 */
interface AvitoBoardEventInterface
{
    public function getEvent(): ?AvitoBoardEventUid;
}
