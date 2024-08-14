<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Entity\Event;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Mapper\AvitoBoardMapper;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardModify;
use BaksDev\Avito\Board\Type\Doctrine\Event\AvitoBoardEventUid;
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

    /**
     * Идентификатор категории Avito
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING)]
    private string $avito;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: AvitoBoardModify::class, mappedBy: 'event', cascade: ['all'])]
    private AvitoBoardModify $modify;

    /**
     * Связь с характеристиками продукта Авито
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: AvitoBoardMapper::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $mapperSetting;

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

    /**
     * @TODO добавить описание поведения
     */
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