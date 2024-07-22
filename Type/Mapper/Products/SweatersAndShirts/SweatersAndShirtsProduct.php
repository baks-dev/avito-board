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

final readonly class SweatersAndShirtsProduct implements SweatersAndShirtsProductInterface
{
    private const string PRODUCT = 'Кофты и футболки';

    private const array LINKS = [
        'Brand' => 'https://autoload.avito.ru/format/brendy_fashion.xml',
    ];

    public function requireFeedElements(): array
    {
        return [];

        //        return [
        //            'Id' => false,
        //            'Address' => false,
        //            'Title' => false,
        //            'Description' => false,
        //            'Condition' => false,
        //            'Images' => false,
        //            'Size' => false,
        //            'Category' => new CategoryFeedElement($this),
        //            'GoodsType' => new GoodsTypeFeedElement($this),
        //            'AdType' => new AdTypeFeedElement(),
        //            'Brand' => new BrandFeedElement($this),
        //            'Apparel' => new ApparelFeedElement($this),
        //            'GoodsSubType' => new GoodsSubTypeFeedElement($this),
        //        ];
    }

    public function product(): string
    {
        return self::PRODUCT;
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
        return self::PRODUCT;
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

    public function link(string $element): string
    {
        return self::LINKS[$element];
    }

    public function isEqualsCategory(string $product): bool
    {
        return self::PRODUCT === $product;
    }

    public function __toString(): string
    {
        return sprintf('%s / %s', $this->category(), $this->goodsType());
    }
}
