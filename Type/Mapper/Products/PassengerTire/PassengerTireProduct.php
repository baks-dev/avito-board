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

declare(strict_types=1);

namespace BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Elements\PassengerTire\CategoryFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\PassengerTire\GoodsTypeFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\PassengerTire\ProductTypeFeedElement;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class PassengerTireProduct implements PassengerTireProductInterface
{
    private const string PASSENGER_TIRE = 'Легковые шины';

    public function __construct(
        #[AutowireIterator('baks.avito.board.elements')] private iterable $elements,
    ) {}

    public function getElements(): array
    {
        $elements = null;

        /** @var AvitoFeedElementInterface $element */
        foreach ($this->elements as $element)
        {

            if ($element->product() === null)
            {
                $elements[] = new $element($this);
            }

            if ($element->product() instanceof self)
            {
                $elements[] = $element;
            }
        }

        if (null === $elements)
        {
            throw new \Exception();
        }

        return $elements;
    }

    public function getElement(string $elementName): ?AvitoFeedElementInterface
    {
        /** @var AvitoFeedElementInterface $element */
        foreach ($this->elements as $element)
        {
            if ($element->element() === $elementName)
            {

                if ($element->product() === null)
                {
                    return new $element($this);
                }

                if ($element->product() instanceof self)
                {
                    return $element;
                }
            }
        }

        throw new \Exception();
    }

    public function getProduct(): string
    {
        return self::PASSENGER_TIRE;
    }

    public function isEqualProduct(string $product): bool
    {
        return $product === self::PASSENGER_TIRE;
    }

    public function __toString(): string
    {
        $category = (new CategoryFeedElement($this))->default();
        $variation = (new GoodsTypeFeedElement($this))->default();
        $type = (new ProductTypeFeedElement($this))->default();

        return sprintf('%s / %s / %s', $category, $variation, $type);
    }
}
