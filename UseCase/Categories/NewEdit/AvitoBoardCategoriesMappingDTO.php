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
use BaksDev\Avito\Board\Type\Event\AvitoBoardCategoriesEventUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class AvitoBoardCategoriesMappingDTO implements AvitoBoardCategoriesEventInterface
{
    #[Assert\Uuid]
    private ?AvitoBoardCategoriesEventUid $id = null;

    /**
     * Параметры карточки
     */
    #[Assert\Valid]
    private ArrayCollection $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * Идентификатор события
     */
    public function getEvent(): ?AvitoBoardCategoriesEventUid
    {
        return $this->id;
    }

    public function setId(AvitoBoardCategoriesEventUid $id): void
    {
        $this->id = $id;
    }

    public function getCategories(): ArrayCollection
    {
        return $this->categories;
    }

    public function addCategories(ArrayCollection $categories): void
    {
        $this->categories = $categories;
    }
}
