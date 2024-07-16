<?php

namespace BaksDev\Avito\Board\Repository\AllPropertiesByCategory;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;

interface AllPropertiesByCategoryInterface
{
    /** Метод возвращает все товары в категории */
    public function fetchAllProductByCategory(CategoryProductUid $category): array;
}