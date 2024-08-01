<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Avito\Board\Entity\Mapper\Properies;

use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Mapper\AvitoBoardMapperInterface;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see MapperElementDTO
 */
#[ORM\Entity]
#[ORM\Table(name: 'avito_board_mapper_properties')]
class AvitoBoardMapperProperties extends EntityEvent
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: AvitoBoardEvent::class, inversedBy: 'properties')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private AvitoBoardEvent $event;

    /**
     * Наименование категории от Авито
     */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\Column(type: Types::STRING)]
    private string $element;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductSectionFieldUid::TYPE, nullable: true)]
    private ?CategoryProductSectionFieldUid $productField = null;


    public function __construct(AvitoBoardEvent $event)
    {
        $this->event = $event;
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if ($dto instanceof AvitoBoardMapperInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof AvitoBoardMapperInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}
