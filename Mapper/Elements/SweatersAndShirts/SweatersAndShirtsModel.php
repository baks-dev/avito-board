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

namespace BaksDev\Avito\Board\Mapper\Elements\SweatersAndShirts;

use BaksDev\Avito\Board\Api\ShirtModelRequest;
use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\SweatersAndShirtsProduct;
use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;

/**
 * Параметр нужно заполнить одинаково во всех объявлениях с разными вариантами товара.
 * Название модели от производителя или ваше. Например: Air Max для кроссовок Nike или «Беговая 21» для футболки.
 * Не используйте символы (#, -, /, *, + и другие), только буквы и цифры. Название модели покупатели не увидят.
 */
final readonly class SweatersAndShirtsModel implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Model';

    private const string LABEL = 'Модель';

    public function __construct(
        private ShirtModelRequest $request,
    ) {}

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
        $search = $this->request->getModel($data->getProductName());

        if(true === empty($search))
        {
            return null;
        }

        if($search['model'])
        {
            return $search['model'];
        }

        return null;
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
        return SweatersAndShirtsProduct::class;
    }
}
