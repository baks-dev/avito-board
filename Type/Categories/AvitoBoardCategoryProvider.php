<?php

namespace BaksDev\Avito\Board\Type\Categories;

use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final readonly class AvitoBoardCategoryProvider
{
    public function __construct(
        #[TaggedIterator('baks.avito.board.categories.type', defaultPriorityMethod: 'priority')] private iterable $categories,
    ) {}

    /**
     * @return list<AvitoBoardCategoryInterface>|null
     */
    public function getElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var AvitoBoardCategoryInterface $category */
        foreach ($this->categories as $category)
        {
            if ($category->getCategory() === $categoryType)
            {
                $elements[] = $category;
            }
        }

        return $elements;
    }

    /**
     * @return list<AvitoBoardCategoryInterface>|null
     */
    public function getCategories(): ?array
    {
        $categories = null;

        /** @var AvitoBoardCategoryInterface $category */
        foreach ($this->categories as $category)
        {
            $categories[$category->getTitle()] = $category;
        }

        return $categories;
    }
}
