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
     * @return list<AvitoBoardFeedElementInterface>|null
     */
    public function getFeedElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var AvitoBoardFeedElementInterface $category */
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
     * @return list<AvitoBoardFeedElementInterface>|null
     */
    public function getCategories(): ?array
    {
        $categories = null;

        /** @var AvitoBoardFeedElementInterface $category */
        foreach ($this->categories as $category)
        {
            $categories[$category->getTitle()] = $category;
        }

        return $categories;
    }
}
