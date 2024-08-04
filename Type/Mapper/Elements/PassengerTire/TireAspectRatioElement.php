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
use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProductInterface;

class TireAspectRatioElement implements AvitoBoardElementInterface
{
    private const string TIRE_ASPECT_RATIO_ELEMENT = 'TireAspectRatio';

    private const string TIRE_ASPECT_RATIO_LABEL = 'Высота профиля шины';

    public function __construct(
        private readonly ?PassengerTireProductInterface $product = null,
        protected ?string $data = null,
    ) {}

    public function isMapping(): true
    {
        return true;
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
        return 'https://www.avito.ru/web/1/autoload/user-docs/category/67016/field/732/values-xml';
    }

    public function setData(string|array $mapper): void
    {
        $this->data = $mapper;
    }

    public function fetchData(): string
    {
        if (null === $this->data)
        {
            throw new \Exception('Не вызван метод setData');
        }

        return preg_replace('/\D/', '', $this->data);
    }

    public function element(): string
    {
        return self::TIRE_ASPECT_RATIO_ELEMENT;
    }

    public function label(): string
    {
        return self::TIRE_ASPECT_RATIO_LABEL;
    }

    public function getProduct(): PassengerTireProductInterface
    {
        return $this->product;
    }
}
