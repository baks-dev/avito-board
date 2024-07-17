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

namespace BaksDev\Avito\Board\UseCase\Categories\NewEdit;

use BaksDev\Avito\Board\Entity\Event\AvitoBoardCategoriesEventInterface;
use BaksDev\Avito\Board\Type\Categories\AvitoBoardFeedElementInterface;
use BaksDev\Avito\Board\Type\Event\AvitoBoardCategoriesEventUid;
use BaksDev\Avito\Board\UseCase\Categories\NewEdit\Elements\AvitoBoardMapperElementDTO;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class AvitoBoardMapperDTO implements AvitoBoardCategoriesEventInterface
{
    #[Assert\Uuid]
    private ?AvitoBoardCategoriesEventUid $id = null;

    /**
     * ID локальной категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly CategoryProductUid $localCategory;

    /**
     * Идентификатор категории на Avito
     */
    #[Assert\NotBlank]
    private string $avitoCategory;

    /**
     * Коллекция элементов формы с сопоставлением категорий для рендеринга в форме
     */
    #[Assert\Valid]
    private ArrayCollection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function setId(AvitoBoardCategoriesEventUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): ?AvitoBoardCategoriesEventUid
    {
        return $this->id;
    }

    public function setLocalCategory(CategoryProductUid|CategoryProduct $category): void
    {
        $this->localCategory = $category instanceof CategoryProduct ? $category->getId() : $category;
    }

    public function getLocalCategory(): CategoryProductUid
    {
        return $this->localCategory;
    }

    public function setAvitoCategory(string $avitoCategory): void
    {
        $this->avitoCategory = $avitoCategory;
    }

    public function getAvitoCategory(): string
    {
        return $this->avitoCategory;
    }

    public function getCategories(): ArrayCollection
    {
        return $this->categories;
    }

    public function addCategory(AvitoBoardMapperElementDTO $element): void
    {
        $this->categories->add($element);
    }

    public function removeCategory(AvitoBoardMapperElementDTO $element): void
    {
        $this->categories->removeElement($element);
    }
}
