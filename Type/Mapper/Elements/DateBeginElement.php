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

/**
 * Это дата и время размещения объявления. Чтобы оно опубликовалось в начале дня по Москве, укажите дату в одном из форматов:
 * — dd.MM.yyyy
 * — dd.MM.yy
 * — yyyy-MM-dd
 *
 * Чтобы публикация произошла с точностью до часа, добавьте время через пробел в формате HH:mm:ss или HH:mm.
 * Если хотите явно указать часовой пояс, используйте формат ISO 8601: YYYY-MM-DDTHH:mm:ss+hh:mm.
 *
 * Несколько важных моментов:
 * — Если в указанную дату автозагрузка будет выключена или тариф не оплачен, объявление не опубликуется.
 * — Если дата из DateBegin ещё не наступила, а объявление уже размещено, оно закроется. Когда эта дата наступит, объявление снова опубликуется.
 * — Публикация объявления по DateBegin не зависит от расписания загрузки вашего файла и произойдёт в указанную дату и время.
 *
 * Не обязателен для Авито, но обязателен для нас
 */
// @TODO тестировать оправку фида без этого элемента (исключить из AvitoBoardElementInterface)
//      ТЕОРЕТИЧЕСКИ если отправить дату в будущем объявление должно закрыться до отправленной даты
class DateBeginElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'DateBegin';

    private const string LABEL = 'Дата и время размещения';

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

    public function fetchData(array $data): string
    {
        if ($data['product_quantity'] === 0)
        {
            // @TODO на какой срок закрывать объявление? - тестировать
            $date = new \DateTimeImmutable('+1 year');
            return $date->format('Y-m-d H:i:s');
        }

        $date = new \DateTimeImmutable($data['product_date_begin']);
        return $date->format('Y-m-d H:i:s');
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
