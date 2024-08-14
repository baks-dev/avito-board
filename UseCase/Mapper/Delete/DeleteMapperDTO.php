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

namespace BaksDev\Avito\Board\UseCase\Mapper\Delete;

use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEventInterface;
use BaksDev\Avito\Board\Type\Doctrine\Event\AvitoBoardEventUid;
use BaksDev\Avito\Board\UseCase\Mapper\Delete\Modify\ModifyDTO;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Маппим необходимые поля из сущности
 * @see AvitoBoardEvent
 */
final class DeleteMapperDTO implements AvitoBoardEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?AvitoBoardEventUid $id = null;

    /**
     * Идентификатор локальной категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private ?CategoryProductUid $category = null;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    private ModifyDTO $modify;

    public function __construct()
    {
        $this->modify = new ModifyDTO();
    }

    /**
     * Идентификатор события
     */
    public function getEvent(): ?AvitoBoardEventUid
    {
        return $this->id;
    }

    public function setId(AvitoBoardEventUid $id): void
    {
        $this->id = $id;
    }

    public function getCategory(): CategoryProductUid
    {
        return $this->category;
    }

    /**
     * Модификатор
     */
    public function getModify(): ModifyDTO
    {
        return $this->modify;
    }
}