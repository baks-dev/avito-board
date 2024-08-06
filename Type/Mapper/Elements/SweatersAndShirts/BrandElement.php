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

namespace BaksDev\Avito\Board\Type\Mapper\Elements\SweatersAndShirts;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Products\SweatersAndShirts\SweatersAndShirtsProduct;

/**
 *  Бренд.
 *  Одно из значений
 *
 * Элемент общий для всех продуктов Авито
 * @TODO ожидает добавление в характеристики продукта поэтому
 */
class BrandElement implements AvitoBoardElementInterface
{
    private const string BRAND_ELEMENT = 'Brand';

    private const string BRAND_LABEL = 'Бренд одежды';

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
    }

    public function isChoices(): false
    {
        return false;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): string
    {
        return 'Общее значение для всех продуктов в данной категории';
    }

    public function getProduct(): string
    {
        return SweatersAndShirtsProduct::class;
    }

    public function fetchData(string|array $data = null): string
    {
        // @TODO присваивать из свойств продукта, когда бренд будет добавлен - пока из product_name
        return $data['product_name'];
    }

    public function element(): string
    {
        return self::BRAND_ELEMENT;
    }

    public function label(): string
    {
        return self::BRAND_LABEL;
    }
}
