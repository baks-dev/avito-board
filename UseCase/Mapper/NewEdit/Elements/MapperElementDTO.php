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

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements;

use BaksDev\Avito\Board\Entity\Mapper\AvitoBoardMapperInterface;
use BaksDev\Avito\Board\Type\Mapper\AvitoBoardFeedElementInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * @see AvitoBoardMapper
 */
final class MapperElementDTO implements AvitoBoardMapperInterface
{
    /**
     * Элемент для построения фида для Авито
     */
    #[Assert\NotBlank]
    private ?AvitoBoardFeedElementInterface $feedElement = null;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    #[Assert\When(expression: 'this.getDef() === null', constraints: new Assert\NotBlank())]
    private ?CategoryProductSectionFieldUid $productField = null;

    #[Assert\When(expression: 'this.getProductField() === null', constraints: new Assert\NotBlank())]
    private ?string $def = null;

    /** @var ArrayCollection<CategoryProductSectionFieldUid> */
    private ArrayCollection $productFields;

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

    /**
     * @return ArrayCollection<CategoryProductSectionFieldUid>
     */
    public function getProductFields(): ArrayCollection
    {
        return $this->productFields;
    }

    public function setProductFields(ArrayCollection $productFields): void
    {
        $this->productFields = $productFields;
    }
}
