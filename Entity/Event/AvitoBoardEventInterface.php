<?php

namespace BaksDev\Avito\Board\Entity\Event;


use BaksDev\Avito\Board\Type\Event\AvitoBoardEventUid;

/**
 * Интерфейс, который должны реализовывать все DTO, через которые изменяется сущность
 * @see AvitoBoardMapperDTO
 */
interface AvitoBoardEventInterface
{
    public function getEvent(): ?AvitoBoardEventUid;
}
