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

namespace BaksDev\Avito\Board\Type\Mapper\Products\PassengerTyre;

final readonly class PassengerTyreProduct implements PassengerTyreProductInterface
{
    private const string PRODUCT = 'Легковые шины';

    private const array LINKS = [
        'Brand' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/110431/values-xml',
        'Model' => 'https://autoload.avito.ru/format/tyres_make.xml',
        'TireSectionWidth' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/731/values-xml',
    ];

    public function requireFeedElements(): array
    {
        return [];

        //        return [];
        //        return [
        //            'Id' => false,
        //            'Address' => false,
        //            'Description' => false,
        //            'Quantity' => false,
        //            'Condition' => false,
        //            'Category' => new CategoryFeedElement($this),
        //            'GoodsType' => new GoodsTypeFeedElement($this),
        //            'AdType' => new AdTypeFeedElement(),
        //            'ProductType' => new ProductTypeFeedElement($this),
        //            'Brand' => new BrandFeedElement($this),
        //            'Model' => new ModelFeedElement($this),
        //            'TireSectionWidth' => new TireSectionWidthFeedElement($this),
        //            'RimDiameter',
        //            'TireAspectRatio',
        //            'TireType',
        //            'ResidualTread',
        //            'BackRimDiameter',
        //            'BackTireAspectRatio',
        //            'BackTireSectionWidth',
        //        ];
    }

    public function product(): string
    {
        return self::PRODUCT;
    }

    public function category(): string
    {
        return 'Запчасти и аксессуары';
    }

    public function goodsType(): string
    {
        return 'Шины, диски и колёса';
    }

    public function productType(): string
    {
        return self::PRODUCT;
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
        return sprintf('%s / %s / %s', $this->category(), $this->goodsType(), $this->productType());
    }
}
