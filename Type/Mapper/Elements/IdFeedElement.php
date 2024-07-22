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
 * Это уникальный идентификатор, который вы присваиваете каждому объявлению в файле.
 *
 * Он помогает Авито распознавать объявления от загрузки к загрузке.
 * Идентификаторы не должны повторяться и их нельзя менять — тогда вы избежите блокировок и других ошибок.
 * Присвоить Id можно двумя способами:
 * 1) Если вы создаёте свой файл или используете шаблон Авито, придумайте Id самостоятельно.
 * Заранее подумайте над правилами, по которым будете составлять его, — так будет проще добавлять новые Id.
 * Например, можно использовать нумерацию по порядку.
 * 2) Если вы работаете в CRM, ERP или другой системе, там есть идентификатор товара или объявления. Можно использовать его.
 * Id может состоять из цифр, русских и английских букв, а также символов , \ / ( ) [  ] - =. Всего — не более 100 знаков.
 *
 * Элемент обязателен для всех продуктов Авито
 */
final readonly class IdFeedElement implements AvitoFeedElementInterface
{
    public const string FEED_ELEMENT = 'Id';

    public const string FEED_ELEMENT_DESC = 'Идентификатор';

    public function isMapping(): bool
    {
        return false;
    }

    public function isRequired(): bool
    {
        return true;
    }

    public function choices(): null
    {
        return null;
    }

    public function default(): null
    {
        return null;
    }

    public static function priority(): int
    {
        return 1000;
    }
}
