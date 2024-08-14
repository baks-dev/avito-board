<?php

namespace BaksDev\Avito\Board\Entity\Event;

use BaksDev\Avito\Board\Type\Doctrine\Event\AvitoBoardEventUid;

/**
 * Интерфейс, который должны реализовывать все DTO, через которые изменяется сущность
 * @see MapperDTO
 */
interface AvitoBoardEventInterface
{
    public function getEvent(): ?AvitoBoardEventUid;
}