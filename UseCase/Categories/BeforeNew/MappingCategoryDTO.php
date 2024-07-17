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

namespace BaksDev\Avito\Board\UseCase\Categories\BeforeNew;

use BaksDev\Avito\Board\Type\Categories\AvitoBoardCategoryElementInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Validator\Constraints as Assert;

final class MappingCategoryDTO
{
    #[Assert\NotBlank]
    #[Assert\Uuid]
    public ?CategoryProductUid $localCategory = null;

    // @TODO заглушка для категорий от Авито
    #[Assert\NotBlank]
    public ?AvitoBoardCategoryElementInterface $avitoCategory = null;
}