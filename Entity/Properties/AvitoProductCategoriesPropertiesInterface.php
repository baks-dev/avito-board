<?php

namespace BaksDev\Avito\Board\Entity\Property;

// этот интерфейс должны реализовывать DTO, через которые изменяется сущность AvitoCategoryProduct
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;

interface AvitoProductCategoriesPropertiesInterface
{
    public function getField(): ?CategoryProductSectionFieldUid;

    public function getDef(): ?string;
}
