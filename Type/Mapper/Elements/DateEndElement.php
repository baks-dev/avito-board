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

namespace BaksDev\Avito\Board\Type\Mapper\Elements;

use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProductInterface;

/**
Это дата и время окончания размещения. Чтобы объявление закрылось в конце дня по Москве, укажите дату в одном из форматов:
 * — dd.MM.yyyy
 * — dd.MM.yy
 * — yyyy-MM-dd
 *
 * Чтобы объявление закрылось с точностью до часа, добавьте время через пробел в формате HH:mm:ss или HH:mm.
 * Если хотите явно указать часовой пояс, используйте формат ISO 8601: YYYY-MM-DDTHH:mm:ss+hh:mm.
 *
 * Если вы укажете уже прошедшую дату, автозагрузка не обработает объявление.
 *
 * @TODO убрал из реализации AvitoBoardElementInterface, так как будем управлять размещением с помощью DateBeginElement
 */
final readonly class DateEndElement
{
    public const string ELEMENT = 'DateEnd';

    public const string ELEMENT_ALIAS = 'product_date_over';

    private const string LABEL = 'Дата и время окончания размещения';

    public function __construct(
        private ?PassengerTireProductInterface $product = null,
    ) {}

    public function isMapping(): bool
    {
        return true;
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function isChoices(): bool
    {
        return false;
    }

    public function default(): null
    {
        return null;
    }

    public function productData(string|array $data = null): string
    {
        if (null === $data)
        {
            return $data[self::ELEMENT_ALIAS];
        }

        return $data;
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function help(): null
    {
        return null;
    }

    public function product(): PassengerTireProductInterface
    {
        return $this->product;
    }
}
