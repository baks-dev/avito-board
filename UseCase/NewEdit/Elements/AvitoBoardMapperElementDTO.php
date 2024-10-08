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

namespace BaksDev\Avito\Board\UseCase\NewEdit\Elements;

use BaksDev\Avito\Board\Entity\Element\AvitoBoardMapperElementInterface;
use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see AvitoBoardMapperElement
 */
final class AvitoBoardMapperElementDTO implements AvitoBoardMapperElementInterface
{
    #[Assert\NotBlank]
    private ?string $element = null;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    #[Assert\When(expression: 'this.getDef() === null', constraints: new Assert\NotBlank())]
    private ?CategoryProductSectionFieldUid $productField = null;

    /**
     * Значение по умолчанию
     */
    #[Assert\When(expression: 'this.getProductField() === null', constraints: new Assert\NotBlank())]
    private ?string $def = null;

    /**
     * Для доступа к методам объекта в форме @see AvitoBoardMapperElementForm
     */
    private ?AvitoBoardElementInterface $elementInstance = null;

    public function getElementInstance(): ?AvitoBoardElementInterface
    {
        return $this->elementInstance;
    }

    public function setElementInstance(?AvitoBoardElementInterface $elementInstance): void
    {
        $this->elementInstance = $elementInstance;
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

    public function getElement(): ?string
    {
        return $this->element;
    }

    public function setElement(?string $element): void
    {
        $this->element = $element;
    }
}