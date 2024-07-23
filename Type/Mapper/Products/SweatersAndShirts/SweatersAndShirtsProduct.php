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

namespace BaksDev\Avito\Board\Type\Mapper\Products\SweatersAndShirts;

use BaksDev\Avito\Board\Type\Mapper\AvitoBoardProductEnum;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class SweatersAndShirtsProduct implements SweatersAndShirtsProductInterface
{
    private const array LINKS = [
        'Brand' => 'https://autoload.avito.ru/format/brendy_fashion.xml',
    ];

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

            if ($element->product() instanceof AvitoBoardProductEnum &&
                $element->product()->value === AvitoBoardProductEnum::SweatersAndShirts->value)
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

    public function getProduct(): AvitoBoardProductEnum
    {
        return AvitoBoardProductEnum::SweatersAndShirts;
    }

    public function category(): string
    {
        return 'Одежда, обувь, аксессуары';
    }

    public function goodsType(): string
    {
        return 'Мужская одежда';
    }

    public function apparel(): string
    {
        return 'Кофты и футболки';
    }

    public function goodsSubType(): array
    {
        return [
            'Футболка',
            'Поло',
            'Майка',
            'Свитшот',
            'Толстовка / худи',
            'Джемпер',
            'Свитер',
            'Кардиган',
            'Кофта',
        ];
    }

    public function condition(): string
    {
        return 'Новое с биркой';
    }

    public function help(string $element): ?string
    {
        if(false === isset(self::LINKS[$element]))
        {
            return null;
        };

        return self::LINKS[$element];
    }

    public function isEqualProduct(string $product): bool
    {
        return AvitoBoardProductEnum::SweatersAndShirts->value === $product;
    }

    public function __toString(): string
    {
        return sprintf('%s / %s', $this->category(), $this->goodsType());
    }
}
