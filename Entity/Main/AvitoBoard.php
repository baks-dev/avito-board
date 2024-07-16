<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Entity\Main;

use BaksDev\Avito\Board\Type\Event\AvitoBoardCategoriesEventUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'avito_board')]
class AvitoBoard extends EntityEvent
{
    /** ID */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $id;

    /**
     * Идентификатор AvitoProductSetting
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: AvitoBoardCategoriesEventUid::TYPE, unique: true, nullable: false)]
    private ?AvitoBoardCategoriesEventUid $event = null;

    public function __construct(CategoryProduct|CategoryProductUid $categoryProduct)
    {
        $this->id = $categoryProduct instanceof CategoryProduct ? $categoryProduct->getId() : $categoryProduct;
    }

    public function __toString(): string
    {
        return (string)$this->id;
    }

    public function getId(): CategoryProductUid
    {
        return $this->id;
    }

    public function setEvent(AvitoBoardCategoriesEventUid $event): void
    {
        $this->event = $event;
    }

    public function getEvent(): ?AvitoBoardCategoriesEventUid
    {
        return $this->event;
    }
}
