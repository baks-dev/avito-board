<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Entity\Properties;

use BaksDev\Avito\Board\Entity\Event\AvitoBoardCategoriesEvent;
use BaksDev\Avito\Board\Entity\Property\AvitoProductCategoriesPropertiesInterface;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Наследуемся от EntityEvent, чтобы через рефлексию гидрировать необходимую DTO
 */
#[ORM\Entity]
#[ORM\Table(name: 'avito_board_categories_properties')]
class AvitoProductCategoriesProperties extends EntityEvent
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AvitoBoardCategoriesEvent::class, inversedBy: 'settings')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private AvitoBoardCategoriesEvent $event;

    /**
     * Наименование характеристики от Авито
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    private string $category;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductSectionFieldUid::TYPE, nullable: true)]
    private ?CategoryProductSectionFieldUid $field = null;

    /**
     * Значение по умолчанию
     */
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $def = null;


    public function __construct(AvitoBoardCategoriesEvent $event)
    {
        $this->event = $event;
    }

    public function __toString(): string
    {
        return (string)$this->event;
    }

    public function getDto($dto): mixed
    {
        if ($dto instanceof AvitoProductCategoriesPropertiesInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof AvitoProductCategoriesPropertiesInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
