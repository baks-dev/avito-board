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

namespace BaksDev\Avito\Board\UseCase\NewEdit;

use BaksDev\Avito\Board\Entity\Event\AvitoBoardEventInterface;
use BaksDev\Avito\Board\Type\Event\AvitoBoardEventUid;
use BaksDev\Avito\Board\UseCase\NewEdit\Elements\AvitoBoardMapperElementDTO;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Маппим необходимые поля из сущности
 * @see AvitoBoardEvent
 */
final class AvitoBoardMapperDTO implements AvitoBoardEventInterface
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
     * Коллекция элементов маппера для рендеринга в форме
     *
     * @var ArrayCollection<AvitoBoardMapperElementDTO> $mapperElements
     */
    #[Assert\Valid]
    private ArrayCollection $mapperElements;


    public function __construct()
    {
        $this->mapperElements = new ArrayCollection();
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
     * @return ArrayCollection<AvitoBoardMapperElementDTO>
     */
    public function getMapperElements(): ArrayCollection
    {
        return $this->mapperElements;
    }

    public function addMapperElement(AvitoBoardMapperElementDTO $element): void
    {
        $this->mapperElements->add($element);
    }

    public function removeMapperElement(AvitoBoardMapperElementDTO $element): void
    {
        $this->mapperElements->removeElement($element);
    }
}