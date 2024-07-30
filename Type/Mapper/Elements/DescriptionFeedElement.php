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
 * Текстовое описание объявления в соответствии с правилами Авито — строка не более 7500 символов
 *
 * Для объявлений, параметры которых соответствуют оплаченному тарифу, вы можете использовать дополнительное форматирование с помощью HTML-тегов.
 * Для формата XML описание должно быть внутри CDATA. Использовать можно только HTML-теги из списка: p, br, strong, em, ul, ol, li.
 *
 * Элемент обязателен для всех продуктов Авито
 */
final readonly class DescriptionFeedElement implements AvitoFeedElementInterface
{
    public const string FEED_ELEMENT = 'Description';

    private const string LABEL = 'Текстовое описание объявления';

    public function __construct(
        private ?AvitoProductInterface $product = null,
    ) {}

    public function isMapping(): bool
    {
        return false;
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

    public function productData(array $product): string
    {
        $data = $product['product_description'];
//        $data = sprintf('<![CDATA[%s]]>',$product['product_description']);
//        dd($data);
        return $data;
    }

    public function element(): string
    {
        return self::FEED_ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }

    public function help(): ?string
    {
        return $this->product->help(self::FEED_ELEMENT);
    }

    public function product(): null
    {
        return null;
    }
}
