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

namespace BaksDev\Avito\Board\Type\Mapper\Elements\PassengerTire;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProduct;

/**
 * Количество шт. в комплекте
 *
 * Не более 8 шт. в комплекте
 *
 * Элемент обязателен продуктов Авито:
 * - Легковые шины
 */
class QuantityElement implements AvitoBoardElementInterface
{
    private const string QUANTITY_ELEMENT = 'Quantity';

    private const string QUANTITY_LABEL = 'Количество покрышек в комплекте';

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

    public function getDefault(): string
    {
        return 'за 1 шт.';
    }

    public function getHelp(): null
    {
        return null;
    }

    public function getProduct(): string
    {
        return PassengerTireProduct::class;
    }

    public function fetchData(string|array $data = null): ?string
    {
        return $data;
    }

    public function element(): string
    {
        return self::QUANTITY_ELEMENT;
    }

    public function label(): string
    {
        return self::QUANTITY_LABEL;
    }
}
