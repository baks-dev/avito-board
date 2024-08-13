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
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Размер
 * Одно из значений
 *
 * Men
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
 *
 * Women
 * <Size>38 (XXS)</Size>
 * <Size>40 (XS)</Size>
 * <Size>42 (S)</Size>
 * <Size>44 (S/M)</Size>
 * <Size>46 (M)</Size>
 * <Size>48 (L)</Size>
 * <Size>50 (L/XL)</Size>
 * <Size>52 (XL)</Size>
 * <Size>54 (XXL)</Size>
 * <Size>56 (3XL)</Size>
 * <Size>58 (4XL)</Size>
 * <Size>60 (5XL)</Size>
 * <Size>62 (5XL)</Size>
 * <Size>64 (6XL)</Size>
 * <Size>66 (6XL)</Size>
 * <Size>68 (7XL)</Size>
 * <Size>70 (7XL)</Size>
 * <Size>72 (8XL)</Size>
 * <Size>74 (8XL)</Size>
 * <Size>76 (8XL)</Size>
 * <Size>78+ (8XL+)</Size>
 * <Size>One size</Size>
 * <Size>Без размера</Size>
 * 40(XXS) 42(XS) 44(XS/S) 46(S) 48(M) 50(L) 52(L/XL) 54(XL) 56(XXL) 58(XXL) 60(3XL) 62(4XL) 64(5XL) 66(6XL) 68(7XL) 70(7XL) 72(8XL) 74(8XL) 76(9XL) 78(10XL) 80(10XL) 82+(10XL+)
 *
 */
// @TODO разобраться откуда брать значение
class SizeElement implements AvitoBoardElementInterface
{
    private const string ELEMENT = 'Size';

    private const string LABEL = 'Размер';

    public function __construct(
        private TranslatorInterface $translator,
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

    public function getHelp(): null
    {
        return null;
    }

    public function fetchData(array $data): ?string
    {
        if (null === $data[self::ELEMENT])
        {
            return null;
        }

        $size = $data[self::ELEMENT];

        $trans = $this->translator->trans($size, [], 'avito-board.mapper.men');

        if ($size === $trans)
        {
            return 'Без размера';
        }

        return $trans;
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
