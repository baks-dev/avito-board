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

namespace BaksDev\Avito\Board\Type\Mapper\Products;

use BaksDev\Avito\Board\Type\Mapper\Elements\AdTypeFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\BrandFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\CategoryFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\GoodsTypeFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\ModelFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\ProductTypeFeedElement;
use BaksDev\Avito\Board\Type\Mapper\Elements\TireSectionWidthFeedElement;

final readonly class PassengerTyre implements PassengerTyreProductInterface
{
    private const array LINKS = [
        'Brand' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/110431/values-xml',
        'Model' => 'https://autoload.avito.ru/format/tyres_make.xml',
        'TireSectionWidth' => 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/731/values-xml',
    ];

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
        return 'Легковые шины';
    }

    public function requireFeedElements(): array
    {
        return [
            'Id',
            'Address',
            'Category' => CategoryFeedElement::class,
            'Description',
            'GoodsType' => GoodsTypeFeedElement::class,
            'AdType' => AdTypeFeedElement::class,
            'ProductType' => ProductTypeFeedElement::class,
            'Brand' => BrandFeedElement::class,
            'Model' => ModelFeedElement::class,
            'TireSectionWidth' => TireSectionWidthFeedElement::class,
            'RimDiameter',
            'TireAspectRatio',
            'TireType',
            'Quantity',
            'ResidualTread',
            'BackRimDiameter',
            'BackTireAspectRatio',
            'BackTireSectionWidth',
            'Condition',
        ];
    }

    public function link(string $element): string
    {
        return self::LINKS[$element];
    }

    public static function priority(): int
    {
        return 996;
    }
}
