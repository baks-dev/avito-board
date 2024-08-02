<?php

namespace BaksDev\Avito\Board\Type\Mapper;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Elements\PassengerTire\TireSectionWidthElement;
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

    /**
     * @return list<AvitoBoardElementInterface>
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

        throw new \Exception('Не найдены элементы, относящиеся к категории ' .$productCategory);
    }

    // @TODO подумать, как еще можно получать инстанс элемента
    public function getOneElement(string $productCategory, string $elementName): AvitoBoardElementInterface
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

        throw new \Exception('Не найден элемент с названием: '. $elementName);
    }

    /** @return list<AvitoProductInterface> */
    public function getProducts(): array
    {
        return iterator_to_array($this->products);
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

        throw new \Exception('Не найдены категория продукта с названием ' . $productCategory);
    }
}
