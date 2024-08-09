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

use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProduct;

/**
 * Диаметр задней оси, дюймы.
 *
 * Применимо, если в поле DifferentWidthTires указано значение 'Да'
 *
 * @see RimDiameterElement
 */
// @TODO не реализует AvitoBoardElementInterface, так как не реализуем разноширокие комплекты
class BackRimDiameterElement
{
    public const string ELEMENT = 'BackRimDiameter';

    public const string LABEL = 'Диаметр шины задней оси';

    public function isMapping(): true
    {
        return true;
    }

    public function isRequired(): false
    {
        return false;
    }

    public function isChoices(): false
    {
        return false;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function getProduct(): string
    {
        return PassengerTireProduct::class;
    }

    // @TODO Если элемент обязательный, то значение будем брать такое же, как и в элементе RimDiameterElement
    public function fetchData(string|array $data = null): ?string
    {
        if(null === $data[self::ELEMENT])
        {
            return null;
        }

        return preg_replace('/\D/', '', $data[self::ELEMENT]);
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }
}
