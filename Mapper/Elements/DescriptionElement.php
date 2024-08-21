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

namespace BaksDev\Avito\Board\Mapper\Elements;

/**
 * Текстовое описание объявления в соответствии с правилами Авито — строка не более 7500 символов
 *
 * Для объявлений, параметры которых соответствуют оплаченному тарифу, вы можете использовать дополнительное форматирование с помощью HTML-тегов.
 * Для формата XML описание должно быть внутри CDATA. Использовать можно только HTML-теги из списка: p, br, strong, em, ul, ol, li.
 *
 * Элемент обязателен для всех продуктов Авито
 *
 *   Список элементов для категории "Легковые шины"
 *   https://www.avito.ru/autoload/documentation/templates/67016?onlyRequiredFields=false&fileFormat=xml
 */
class DescriptionElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Description';

    private const string LABEL = 'Текстовое описание объявления';

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

    public function getHelp(): ?string
    {
        return null;
    }

    public function fetchData(array $data): ?string
    {
        if(null === $data['product_description'])
        {
            return null;
        }

        $desc = strip_tags($data['product_description'], ['<p>', '<br>', '<strong>', '<em>', '<ul>', '<ol>', '<li>']);

        return sprintf('<![CDATA[%s]]>', $desc);
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
