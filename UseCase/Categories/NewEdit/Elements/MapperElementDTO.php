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

namespace BaksDev\Avito\Board\UseCase\Categories\NewEdit\Elements;

use BaksDev\Avito\Board\Entity\Categories\AvitoBoardProductCategoriesMappingInterface;
use BaksDev\Avito\Board\Type\Categories\AvitoBoardFeedElementInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

final class MapperElementDTO implements AvitoBoardProductCategoriesMappingInterface
{
    /**
     * @TODO нужно понять, какое значение сохранять в БД
     * Элемент для построения фида для Авито
     */
    #[Assert\NotBlank]
    private ?AvitoBoardFeedElementInterface $feedElement = null;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    private ?CategoryProductSectionFieldUid $productField = null;

    private ?string $def = null;

    public function getFeedElement(): ?AvitoBoardFeedElementInterface
    {
        return $this->feedElement;
    }

    public function setFeedElement(?AvitoBoardFeedElementInterface $feedElement): void
    {
        $this->feedElement = $feedElement;
    }

    public function getProductField(): ?CategoryProductSectionFieldUid
    {
        return $this->productField;
    }

    public function setProductField(?CategoryProductSectionFieldUid $productField): void
    {
        $this->productField = $productField;
    }

    public function getDef(): ?string
    {
        return $this->def;
    }

    public function setDef(?string $default): self
    {
        $this->def = $default;
        return $this;
    }
}
