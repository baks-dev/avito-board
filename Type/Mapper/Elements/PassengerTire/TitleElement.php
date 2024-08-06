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
use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireBoardProduct;

/**
 * Название объявления — строка до 50 символов.
 * Примечание: не пишите в название цену и контактную информацию — для этого есть отдельные поля — и не используйте слово «продам».
 *
 * Элемент обязателен для продуктов:
 * - Кофты и футболки
 */
class TitleElement implements AvitoBoardElementInterface
{
    private const string TITLE_ELEMENT = 'Title';

    private const string TITLE_LABEL = 'Название объявления';

    // @TODO подумать давать выбор для маппинга свойству продукта или брать значение по ключу методом ->productData
    // @todo если давать выбор из свойства продукта - как его добавить в выпадающий список
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

    public function getHelp(): null
    {
        return null;
    }

    public function getProduct(): string
    {
        return PassengerTireBoardProduct::class;
    }

    public function fetchData(string|array $data = null): ?string
    {
        // @TODO подумать по какому ключу формировать значение
        return sprintf('%s %s', $data['product_name'], $data['product_article']);
    }

    public function element(): string
    {
        return self::TITLE_ELEMENT;
    }

    public function label(): string
    {
        return self::TITLE_LABEL;
    }
}
