<?php

namespace BaksDev\Avito\Board\Type\Mapper;

use BaksDev\Avito\Board\Type\Mapper\Product\AvitoProductInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @see AvitoBoardFeedElementInterface
 * @see AvitoProductInterface
 */
final readonly class AvitoBoardMapperProvider
{
    public function __construct(
        #[AutowireIterator('baks.avito.board.elements', defaultPriorityMethod: 'priority')]
        private iterable $feedElements,
        #[AutowireIterator('baks.avito.board.products', defaultPriorityMethod: 'priority')]
        private iterable $categories,
    ) {}

    /**
     * @return list<AvitoBoardFeedElementInterface>|null
     */
    public function getFeedElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var AvitoBoardFeedElementInterface $element */
        foreach ($this->feedElements as $element)
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
        foreach ($this->feedElements as $category)
        {
            $categories[$category->getSubCategory()] = $category;
        }

        return $categories;
    }

    public function getElements(string $categoryType): ?array
    {
        $elements = null;

        /** @var AvitoProductInterface $category */
        foreach ($this->categories as $category)
        {
            dump($category);

        }

        dd();
        return $elements;
    }
}
