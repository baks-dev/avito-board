<?php

namespace BaksDev\Avito\Board\Type\Mapper;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Products\AvitoProductInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @see PassengerTyreProductInterface
 * @see SweatersAndShirtsProductInterface
 * @see AvitoFeedElementInterface
 */
final readonly class AvitoBoardMapperProvider
{
    public function __construct(
        #[AutowireIterator('baks.avito.board.products')] private iterable $products,
        #[AutowireIterator('baks.avito.board.elements')] private iterable $elements,
    ) {}

    /** @return list<AvitoProductInterface> */
    public function getProducts(): array
    {
        return iterator_to_array($this->products);
    }

    /** @return list<AvitoFeedElementInterface> */
    public function getElements(string $productCategory): ?array
    {
        $elements = null;

        /** @var AvitoProductInterface $product */
        foreach ($this->products as $product)
        {

            dump($element);

        }

        dd();
        return $elements;
    }
}
