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

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit;

use BaksDev\Avito\Board\Entity\Event\AvitoBoardEventInterface;
use BaksDev\Avito\Board\Type\Doctrine\Event\AvitoBoardEventUid;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Properies\MapperPropertiesDTO;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Маппим необходимые поля из сущности
 * @see AvitoBoardEvent
 */
final class MapperDTO implements AvitoBoardEventInterface
{
    #[Assert\Uuid]
    private ?AvitoBoardEventUid $id = null;

    /**
     * ID локальной категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private CategoryProductUid $category;

    /**
     * Идентификатор категории на Avito
     */
    #[Assert\NotBlank]
    private string $avito;

    /**
     * Коллекция мапперов - соотношений свойств для рендеринга в форме
     * @var ArrayCollection<MapperElementDTO> $mapperSetting
     */
    #[Assert\Valid]
    private ArrayCollection $mapperSetting;

    /**
     * @var ArrayCollection<MapperPropertiesDTO> $mapperSetting
     */
    #[Assert\Valid]
    private ArrayCollection $properties;

    public function __construct()
    {
        $this->mapperSetting = new ArrayCollection();
        $this->properties = new ArrayCollection();
    }

    public function setId(AvitoBoardEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?AvitoBoardEventUid
    {
        return $this->id;
    }

    public function setCategory(CategoryProductUid|CategoryProduct $category): void
    {
        $this->category = $category instanceof CategoryProduct ? $category->getId() : $category;
    }

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }

    public function setAvito(string $avito): void
    {
        $this->avito = $avito;
    }

    public function getAvito(): string
    {
        return $this->avito;
    }

    /**
     * @return ArrayCollection<MapperElementDTO>
     */
    public function getMapperSetting(): ArrayCollection
    {
        return $this->mapperSetting;
    }

    public function addMapperSetting(MapperElementDTO $element): void
    {
        $this->mapperSetting->add($element);
    }

    public function removeMapperSetting(MapperElementDTO $element): void
    {
        $this->mapperSetting->removeElement($element);
    }

    /**
     * @return ArrayCollection<MapperPropertiesDTO>
     */
    public function getProperties(): ArrayCollection
    {
        return $this->properties;
    }

    public function addProperty(MapperPropertiesDTO $property): void
    {
        $this->properties->add($property);
    }

    public function removeProperty(MapperElementDTO $property): void
    {
        $this->mapperSetting->removeElement($property);
    }
}