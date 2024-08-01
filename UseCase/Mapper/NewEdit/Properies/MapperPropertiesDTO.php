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

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Properies;

use BaksDev\Avito\Board\Entity\Mapper\Properies\AvitoBoardMapperPropertiesInterface;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @see AvitoBoardMapper
 */
final class MapperPropertiesDTO implements AvitoBoardMapperPropertiesInterface
{
    #[Assert\NotBlank]
    private ?string $element = null;

    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    #[Assert\NotBlank]
    private ?CategoryProductSectionFieldUid $productField = null;


    /**
     *  Для передачи в форму
     * @see MapperElementForm
     *
     * Элемент соответствия для построения фида для Авито
     */
    #[Assert\NotBlank]
    private ?AvitoFeedElementInterface $elementInstance = null;

    public function getElementInstance(): ?AvitoFeedElementInterface
    {
        return $this->elementInstance;
    }

    public function setElementInstance(?AvitoFeedElementInterface $elementInstance): void
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

    public function getElement(): ?string
    {
        return $this->element;
    }

    public function setElement(?string $element): void
    {
        $this->element = $element;
    }
}
