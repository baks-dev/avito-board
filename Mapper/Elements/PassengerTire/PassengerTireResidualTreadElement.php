<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Board\Mapper\Elements\PassengerTire;

use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\PassengerTireProduct;

/**
 * Остаточная глубина протектора шины.
 *
 * Может принимать значения от 1 до 50 включительно, измеряется в миллиметрах (мм)
 * В диапазоне 1-10 мм включительно можно использовать дробные значения.
 *
 * Применимо, если в поле Condition указано значение 'Б/у'
 *
 *  Список элементов для категории "Легковые шины"
 *  https://www.avito.ru/autoload/documentation/templates/67016?onlyRequiredFields=false&fileFormat=xml
 */
class PassengerTireResidualTreadElement implements AvitoBoardElementInterface
{
    public const string ELEMENT = 'ResidualTread';

    public const string LABEL = 'Остаточная глубина протектора шины';

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
    }

    // Т.к. не реализуем б/у, значение будет максимально возможное
    public function getDefault(): string
    {
        return '50';
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(array $data): string
    {
        return $this->getDefault();
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function getProduct(): string
    {
        return PassengerTireProduct::class;
    }
}
