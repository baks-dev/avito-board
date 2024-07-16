<?php

namespace BaksDev\Avito\Board\Entity\Event;

use BaksDev\Avito\Board\Type\Event\AvitoBoardCategoriesEventUid;

/**
 * Интерфейс, который должны реализовывать DTO, через которые изменяется сущность
 * @see AvitoBoardCategoriesEvent
 */
interface AvitoBoardCategoriesEventInterface
{
    public function getEvent(): ?AvitoBoardCategoriesEventUid;
}
