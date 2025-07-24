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
use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;

/**
 * Количество шт. в комплекте
 *
 * Одно из значений:
 * — за 1 шт.
 * — за 2 шт.
 * — за 3 шт.
 * — за 4 шт.
 * — за 5 шт.
 * — за 6 шт.
 * — за 7 шт.
 * — за 8 шт.
 *
 * Не более 8 шт. в комплекте
 *
 *  Список элементов для категории "Легковые шины"
 *  https://www.avito.ru/autoload/documentation/templates/67016?onlyRequiredFields=false&fileFormat=xml
 */
class PassengerTireQuantityElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Quantity';

    private const string LABEL = 'Количество покрышек в комплекте';

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
    }

    public function getDefault(): string
    {
        return 'за 1 шт.';
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): string
    {
        $kit = $data->getAvitoKitValue();

        /** Если параметр Количество товаров в объявлении НЕ УСТАНОВЛЕН - значить в объявлении 1 товар */
        $quantity = $this->getDefault();

        /** Если параметр Количество товаров в объявлении УСТАНОВЛЕН и не равен 1
         * - значит в объявлении количество товаров из avito_kit_value
         */
        if((false === empty($kit)) && $kit !== 1)
        {
            $quantity = sprintf('за %s шт.', $kit);
        }

        return $quantity;
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
