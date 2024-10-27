<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Avito\Board\Entity\Event;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Element\AvitoBoardMapperElement;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardModify;
use BaksDev\Avito\Board\Type\Event\AvitoBoardEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_board_event')]
class AvitoBoardEvent extends EntityEvent
{
    /**
     * Идентификатор События - Uuid Value Object
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: AvitoBoardEventUid::TYPE)]
    private AvitoBoardEventUid $id;

    /**
     * Идентификатор локальной категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $category;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private string $avito;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: AvitoBoardModify::class, mappedBy: 'event', cascade: ['all'])]
    private AvitoBoardModify $modify;

    /**
     * Связь с характеристиками продукта от Авито
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: AvitoBoardMapperElement::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $mapperElements;

    public function __construct()
    {
        $this->id = new AvitoBoardEventUid();
        $this->modify = new AvitoBoardModify($this);
    }

    /**
     * Используется при модификации - update
     */
    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    /**
     * Магический метод гидрирования DTO с помощью рефлексии
     */
    public function getDto($dto): mixed
    {
        if($dto instanceof AvitoBoardEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof AvitoBoardEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getId(): AvitoBoardEventUid
    {
        return $this->id;
    }

    /**
     * Сеттер для корневой сущности, к которой относится данное событие
     */
    public function setMain(AvitoBoard|CategoryProductUid $main): void
    {
        $this->category = $main instanceof AvitoBoard ? $main->getId() : $main;
    }

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }

    public function getAvito(): string
    {
        return $this->avito;
    }
}
