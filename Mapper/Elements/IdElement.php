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
 *
 *   Список элементов для категории "Легковые шины"
 *   https://www.avito.ru/autoload/documentation/templates/67016?onlyRequiredFields=false&fileFormat=xml
 */
class IdElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Id';

    private const string LABEL = 'Идентификатор';

    public function isMapping(): bool
    {
        return false;
    }

    public function isRequired(): bool
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

    public function fetchData(AllProductsWithMapperResult|array $data): string|null
    {
        $id = $data->getProductArticle();
        $kit = $data->getAvitoKitValue();

        /** Если параметр Количество товаров в объявлении УСТАНОВЛЕН и не равен 1 - объявление дублируется, к артикулу добавляется значение avito_kit_value */
        if((false === empty($kit)) && $kit !== 1)
        {
            $id = (sprintf('%s-KIT-%s', $id, $kit));
        }

        return $id;
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
