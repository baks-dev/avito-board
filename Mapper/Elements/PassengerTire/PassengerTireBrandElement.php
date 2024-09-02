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

namespace BaksDev\Avito\Board\Mapper\Elements\PassengerTire;

use BaksDev\Avito\Board\Api\TireModelRequest;
use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Mapper\Products\PassengerTireProduct;

/**
 *  Бренд.
 *
 *  Одно из значений
 */
final readonly class PassengerTireBrandElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Brand';

    private const string LABEL = 'Бренд шины';

    public function __construct(
        private TireModelRequest $request,
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

    public function getHelp(): string
    {
        return '@avito-board/admin/mapper/elements/tire/brand.html.twig';
    }

    public function fetchData(array $data): ?string
    {
        $search = $this->request->getModel($data['product_name']);

        if (null == $search)
        {
            return null;
        }

        return $search['brand'];
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
        return PassengerTireProduct::class;
    }
}