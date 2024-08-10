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

namespace BaksDev\Avito\Board\Type\Mapper\Elements\SweatersAndShirts;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Products\SweatersAndShirts\SweatersAndShirtsProduct;

/**
 * Размер. Мужская одежда
 * Одно из значений
 *
 * <Size>40 (XXS)</Size>
 * <Size>42 (XS)</Size>
 * <Size>44 (XS/S)</Size>
 * <Size>46 (S)</Size>
 * <Size>48 (M)</Size>
 * <Size>50 (L)</Size>
 * <Size>52 (L/XL)</Size>
 * <Size>54 (XL)</Size>
 * <Size>56 (XXL)</Size>
 * <Size>58 (XXL)</Size>
 * <Size>60 (3XL)</Size>
 * <Size>62 (4XL)</Size>
 * <Size>64 (5XL)</Size>
 * <Size>66 (6XL)</Size>
 * <Size>68 (7XL)</Size>
 * <Size>70 (7XL)</Size>
 * <Size>72 (8XL)</Size>
 * <Size>74 (8XL)</Size>
 * <Size>76 (9XL)</Size>
 * <Size>78 (10XL)</Size>
 * <Size>80 (10XL)</Size>
 * <Size>82+ (10XL+)</Size>
 * <Size>One size</Size>
 * <Size>Без размера</Size>
 */
// @TODO разобраться откуда брать значение
class SizeElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Size';

    private const string LABEL = 'Размер. Мужская одежда';

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

    // @TODO либо Без размера, либо из свойств продукта через метод fetchData в соответствии со значениями из Авито
    public function getDefault(): string
    {
        return 'Без размера';
    }

    public function getHelp(): null
    {
        return null;
    }

    public function getProduct(): string
    {
        return SweatersAndShirtsProduct::class;
    }

    public function fetchData(string|array $data = null): ?string
    {
        return null;

        //        if(in_array('_size', $data))
        //        {
        //            return $data['_size'];
        //        }
        //
        //        return 'Без размера';
    }

    public function element(): string
    {
        return self::ELEMENT;
    }

    public function label(): string
    {
        return self::LABEL;
    }
}
