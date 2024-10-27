<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Avito\Board\Mapper;

use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\AvitoBoardProductInterface;
use Exception;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * @see PassengerTireProductInterface
 * @see SweatersAndShirtsProductInterface
 */
final readonly class AvitoBoardMapperProvider
{
    public function __construct(
        #[AutowireIterator('baks.avito.board.mapper.products')] private iterable $products,
    ) {}

    /** @return list<AvitoBoardProductInterface> */
    public function getProducts(): array
    {
        return iterator_to_array($this->products);
    }

    public function getProduct(string $productCategory): AvitoBoardProductInterface
    {
        foreach($this->products as $product)
        {
            if($product->isEqual($productCategory))
            {
                return $product;
            }
        }

        throw new Exception('Не найдена категория продукта с названием '.$productCategory);
    }

    /**
     * @return list<AvitoBoardElementInterface>
     */
    public function filterElements(string $productCategory): array
    {
        /** @var AvitoBoardProductInterface $product */
        foreach($this->products as $product)
        {
            if($product->isEqual($productCategory))
            {
                return $product->getElements();
            }
        }

        throw new Exception('Не найдены элементы, относящиеся к категории '.$productCategory);
    }

    public function getElement(string $productCategory, string $elementName): AvitoBoardElementInterface
    {
        /** @var AvitoBoardProductInterface $product */
        foreach($this->products as $product)
        {
            if($product->isEqual($productCategory))
            {
                $allElements = $product->getElements();

                foreach($allElements as $element)
                {
                    if($element->element() === $elementName)
                    {
                        return $element;
                    }
                }
            }
        }

        throw new Exception('Не найден элемент с названием: '.$elementName);
    }
}
