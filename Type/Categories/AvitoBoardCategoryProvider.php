<?php

namespace BaksDev\Avito\Board\Type\Categories;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class AvitoBoardCategoryProvider
{
    public function __construct(
        #[AutowireIterator('baks.avito.board.categories.type', defaultPriorityMethod: 'priority')]
        private iterable $categories,
    ) {}

    /**
     * @return list<AvitoBoardCategoryElementInterface>|null
     */
    public function getElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var AvitoBoardCategoryElementInterface $category */
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
     * @return list<AvitoBoardCategoryElementInterface>|null
     */
    public function getCategories(): ?array
    {
        $categories = null;

        /** @var AvitoBoardCategoryElementInterface $category */
        foreach ($this->categories as $category)
        {
            $categories[$category->getTitle()] = $category;
        }

        return $categories;
    }
}
