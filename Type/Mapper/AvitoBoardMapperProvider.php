<?php

namespace BaksDev\Avito\Board\Type\Mapper;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Products\AvitoProductInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @see PassengerTireProductInterface
 * @see SweatersAndShirtsProductInterface
 */
final readonly class AvitoBoardMapperProvider
{
    public function __construct(
        #[AutowireIterator('baks.avito.board.products')] private iterable $products,
    ) {}

    /** @return list<AvitoProductInterface> */
    public function getProducts(): array
    {
        return iterator_to_array($this->products);
    }

    /**
     * @return list<AvitoFeedElementInterface>
     */
    public function filterElements(string $productCategory): array
    {
        /** @var AvitoProductInterface $product */
        foreach ($this->products as $product)
        {
            if ($product->isEqualProduct($productCategory))
            {
                return $product->getElements();
            }
        }

        throw new \Exception('Avito elements not found');
    }

    public function getFeedElement(string $productCategory, string $elementName): AvitoFeedElementInterface
    {
        /** @var AvitoProductInterface $product */
        foreach ($this->products as $product)
        {
            if ($product->isEqualProduct($productCategory))
            {
                $allElements = $product->getElements();

                foreach ($allElements as $element)
                {
                    if ($element->element() === $elementName)
                    {
                        return $element;
                    }
                }
            }
        }

        throw new \Exception('Avito elements not found');
    }

    public function getProduct(string $productCategory): AvitoProductInterface
    {
        foreach ($this->products as $product)
        {
            if ($product->isEqualProduct($productCategory))
            {
                return $product;
            }
        }

        throw new \Exception('Avito product not found');
    }
}
