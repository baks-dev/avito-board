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
 * Название объявления — строка до 50 символов.
 * Примечание: не пишите в название цену и контактную информацию — для этого есть отдельные поля — и не используйте слово «продам».
 */
final readonly class PassengerTireTitleElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Title';

    private const string LABEL = 'Название объявления';

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): true
    {
        return true;
    }

    public function getDefault(): null
    {
        return null;
    }

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(AllProductsWithMapperResult $data): ?string
    {
        $kit = $data->getAvitoKitValue();

        $name = 'Шины '.$data->getProductName();

        /** Если параметр Количество товаров в объявлении УСТАНОВЛЕН и не равен 1 - объявление дублируется, цена умножается на значение avito_kit_value */
        if((false === empty($kit)) && $kit !== 1)
        {
            $name = 'Комплект шин '.$data->getProductName();
        }

        if($data->getProductVariationValue())
        {
            $name .= ' '.$data->getProductVariationValue();
        }

        if($data->getProductModificationValue())
        {
            $name .= '/'.$data->getProductModificationValue();
        }

        if($data->getProductOfferValue())
        {
            $name .= ' R'.$data->getProductOfferValue();
        }

        if($data->getProductOfferPostfix())
        {
            $name .= ' '.$data->getProductOfferPostfix();
        }

        if($data->getProductVariationPostfix())
        {
            $name .= ' '.$data->getProductVariationPostfix();
        }

        if($data->getProductModificationPostfix())
        {
            $name .= ' '.$data->getProductModificationPostfix();
        }

        return $name;
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
