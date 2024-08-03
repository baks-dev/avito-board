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

use BaksDev\Avito\Board\Type\Mapper\Products\AvitoProductInterface;

/**
 * Полный адрес объекта — строка до 256 символов.
 * Является альтернативой параметрам Latitude, Longitude
 *
 * Элемент обязателен для всех продуктов Авито
 */
class AddressElement implements AvitoBoardElementInterface
{
    public const string ELEMENT_ALIAS = '_address_from_avito_token_profile';

    private const string ELEMENT = 'Address';

    private const string ELEMENT_LABEL = 'Полный адрес объекта';

    public function __construct(
        private readonly ?AvitoProductInterface $product = null,
        protected ?string $data = null,
    ) {}

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

    public function setData(string|array $profile): void
    {
        $this->data = (string)$profile[self::ELEMENT_ALIAS];
    }

    public function fetchData(): string
    {
        // @TODO временный адрес, так как адрес еще не откуда брать
        return 'Тамбовская область, Моршанск, Лесная улица, 7';

        //        return $product['_from_avito_token_profile'];
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::ELEMENT_LABEL;
    }

    public function getProduct(): null
    {
        return $this->product;
    }
}
