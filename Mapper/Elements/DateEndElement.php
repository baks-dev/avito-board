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
 *
 */

declare(strict_types=1);

namespace BaksDev\Avito\Board\Mapper\Elements;

use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use DateTimeImmutable;

/**
 * Это дата и время окончания размещения. Чтобы объявление закрылось в конце дня по Москве, укажите дату в одном из форматов:
 * — dd.MM.yyyy
 * — dd.MM.yy
 * — yyyy-MM-dd
 *
 * Чтобы объявление закрылось с точностью до часа, добавьте время через пробел в формате HH:mm:ss или HH:mm.
 * Если хотите явно указать часовой пояс, используйте формат ISO 8601: YYYY-MM-DDTHH:mm:ss+hh:mm.
 *
 * Если вы укажете уже прошедшую дату, автозагрузка не обработает объявление.
 */
class DateEndElement implements AvitoBoardElementInterface
{
    public const string ELEMENT = 'DateEnd';

    private const string LABEL = 'Дата и время окончания размещения';

    public function isMapping(): false
    {
        return false;
    }

    public function isRequired(): false
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

    public function fetchData(AllProductsWithMapperResult $data): ?string
    {
        /** По умолчанию объявление будет добавлено в фид, НО НЕ ОПУБЛИКОВАННО - дата в будущем */
        $date = new DateTimeImmutable('+ 2 day');

        $product_quantity = $data->getProductQuantity();

        /**
         * Публикуем объявление только при наличии и в наличии больше или равное параметру avito_kit_value
         * Срок, на который будет опубликовано объявление - 1 день
         * @see PassengerTireQuantityElement
         */
        if($product_quantity > 0 and $product_quantity >= $data->getAvitoKitValue())
        {
            $date = new DateTimeImmutable('+ 1 day');
        }

        return $date->format('Y-m-d').' 01:00:00';
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function getProduct(): null
    {
        return null;
    }
}
