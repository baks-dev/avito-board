<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Entity\Event;

use BaksDev\Avito\Board\Entity\Categories\AvitoBoardProductCategoriesMapping;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardCategoriesModify;
use BaksDev\Avito\Board\Type\Event\AvitoBoardCategoriesEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_board_categories_event')]
class AvitoBoardCategoriesEvent extends EntityEvent
{
    /**
     * Идентификатор События - Uuid Value Object
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: AvitoBoardCategoriesEventUid::TYPE)]
    private AvitoBoardCategoriesEventUid $id;

    /**
     * Идентификатор локальной категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $category;

    /**
     * Связь с характеристиками продукта от Авито
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: AvitoBoardProductCategoriesMapping::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $categories;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: AvitoBoardCategoriesModify::class, mappedBy: 'event', cascade: ['all'])]
    private AvitoBoardCategoriesModify $modify;

    public function __construct()
    {
        $this->id = new AvitoBoardCategoriesEventUid();
        $this->modify = new AvitoBoardCategoriesModify($this);
    }

    /**
     * Используется при модификации - update
     */
    public function __clone(): void
    {
        $this->id = clone new AvitoBoardCategoriesEventUid();
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    /**
     * Магический метод гидрирования DTO с помощью рефлексии
     */
    public function getDto($dto): mixed
    {
        if ($dto instanceof AvitoBoardCategoriesEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    // магический метод гидрирования DTO с помощью рефлексии
    public function setEntity($dto): mixed
    {
        if ($dto instanceof AvitoBoardCategoriesEventInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function getId(): AvitoBoardCategoriesEventUid
    {
        return $this->id;
    }

    // сеттер для сущности, к которому относится данное событие
    public function setMain(CategoryProduct|CategoryProductUid $main): void
    {
        $this->category = $main instanceof CategoryProduct ? $main->getId() : $main;
    }

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }
}
