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

use BaksDev\Avito\Board\Type\Mapper\AvitoBoardProductEnum;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class PassengerTireProduct implements PassengerTireProductInterface
{
    private const array LINKS = [
        'Brand' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/110431/values-xml',
        'Model' => 'https://autoload.avito.ru/format/tyres_make.xml',
        'TireSectionWidth' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/731/values-xml',
        'TireAspectRatio' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/732/values-xml',
        'RimDiameter' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/733/values-xml',
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
                $element->product()->value === AvitoBoardProductEnum::PassengerTire->value)
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

    public function getElement(string $elementName): AvitoFeedElementInterface
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

                if ($element->product() instanceof AvitoBoardProductEnum &&
                    $element->product()->value === AvitoBoardProductEnum::PassengerTire->value)
                {
                    return $element;
                }
            }
        }

        throw new \Exception();
    }

    public function getProduct(): AvitoBoardProductEnum
    {
        return AvitoBoardProductEnum::PassengerTire;
    }

    public function category(): string
    {
        return 'Запчасти и аксессуары';
    }

    public function goodsType(): string
    {
        return 'Шины, диски и колёса (из класса)';
    }

    public function productType(): string
    {
        return 'Легковые шины';
    }

    public function tireType(): array
    {
        return [
            'Всесезонные',
            'Зимние нешипованные',
            'Зимние шипованные',
            'Летние',
        ];
    }

    public function condition(): string
    {
        return 'Новое';
    }

    public function help(string $element): ?string
    {
        if (false === isset(self::LINKS[$element]))
        {
            return null;
        };

        return self::LINKS[$element];
    }

    public function isEqualProduct(string $product): bool
    {
        return AvitoBoardProductEnum::PassengerTire->value === $product;
    }

    public function __toString(): string
    {
        return sprintf('%s / %s / %s', $this->category(), $this->goodsType(), $this->productType());
    }
}
