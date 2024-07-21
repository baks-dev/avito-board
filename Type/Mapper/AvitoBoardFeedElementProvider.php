<?php

namespace BaksDev\Avito\Board\Type\Mapper;

use BaksDev\Avito\Board\Type\Mapper\Categories\CategoryInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class AvitoBoardFeedElementProvider
{
    public function __construct(
        #[AutowireIterator('baks.avito.board.elements', defaultPriorityMethod: 'priority')]
        private iterable $elements,
        #[AutowireIterator('baks.avito.board.mapper.category', defaultPriorityMethod: 'priority')]
        private iterable $categories,
    ) {}

    /**
     * @return list<AvitoBoardFeedElementInterface>|null
     */
    public function getFeedElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var AvitoBoardFeedElementInterface $element */
        foreach ($this->elements as $element)
        {
            if ($element->getRootCategory() === $categoryType)
            {
                $elements[] = $element;
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
        foreach ($this->elements as $category)
        {
            $categories[$category->getSubCategory()] = $category;
        }

        return $categories;
    }

    public function getElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var CategoryInterface $category */
        foreach ($this->categories as $category)
        {
            if ($category->getRootCategory() === $categoryType)
            {
                $elements[] = $category;
            }
        }

        return $elements;
    }
}
