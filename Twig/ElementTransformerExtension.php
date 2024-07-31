<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Board\Twig;

use BaksDev\Avito\Board\Type\Mapper\AvitoBoardMapperProvider;
use stdClass;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ElementTransformerExtension extends AbstractExtension
{
    public function __construct(
        private readonly AvitoBoardMapperProvider $mapperProvider,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('element_transform', [$this, 'elementTransform']),
            new TwigFunction('element_transform_to_array', [$this, 'elementTransformToArray']),
        ];
    }

    public function elementTransform(array $product)
    {
        /** Возвращаем null, если нет маппера для продукта */
        if ($product['avito_board_avito_category'] === null)
        {
            return null;
        }

        $elements = null;

        foreach ($this->mapperProvider->filterElements($product['avito_board_avito_category']) as $element)
        {
            if (false === $element->isMapping())
            {
                if ($element->default() === null)
                {
                    $tag = new stdClass();
                    $tag->value = $element->productData($product);
                    $tag->element = $element->element();
                    $elements[$element->element()] = $tag;
                }
                else
                {
                    $tag = new stdClass();
                    $tag->value = $element->default();
                    $tag->element = $element->element();
                    $elements[$element->element()] = $tag;
                }
            }
        }

        /** Получаем массив элементов из маппера*/
        $mapperElements = $this->mapperElementTransform($product['avito_board_mapper']);

        /** Добавляем к массиву элементов, не участвующих в маппинге элементы, участвующие в маппинге */
        $elements += $mapperElements;
        //        $elements = array_merge($mapperElements, $elements);

        //        $elements = implode(PHP_EOL, $elements);

//                dd($elements);

        return $elements;
    }

    private function mapperElementTransform(string $string): array
    {
        $mapperElements = json_decode($string, false, 512, JSON_THROW_ON_ERROR);

        $elements = null;

        foreach ($mapperElements as $element)
        {
            $elements[$element->element] = $element;
        }

        return $elements;
    }

    public function elementTransformToArray(array $product): ?array
    {
        /**
         * Возвращаем null, если нет маппера для продукта
         * Данная проверка есть так же на уровне БД
         */
        if ($product['avito_board_avito_category'] === null)
        {
            return null;
        }

        $elements = null;

        foreach ($this->mapperProvider->filterElements($product['avito_board_avito_category']) as $element)
        {
            if (false === $element->isMapping())
            {
                if ($element->default() === null)
                {
                    $elements[$element->element()] = sprintf('<%s>%s</%s>', $element->element(), $element->productData($product), $element->element());
                }
                else
                {
                    $elements[$element->element()] = sprintf('<%s>%s</%s>', $element->element(), $element->default(), $element->element());
                }
            }
        }

        /** Получаем массив элементов из маппера*/
        $mapperElements = $this->mapperElementTransformToArray($product['avito_board_mapper']);

        /** Добавляем к массиву элементов, не участвующих в маппинге элементы, участвующие в маппинге */
        $elements += $mapperElements;
        //        $elements = array_merge($mapperElements, $elements);

        //        $elements = implode(PHP_EOL, $elements);

        //        dd($elements);

        return $elements;
    }

    private function mapperElementTransformToArray(string $string): array
    {
        $mapperElements = json_decode($string, false, 512, JSON_THROW_ON_ERROR);

        $elements = null;

        foreach ($mapperElements as $element)
        {
            $tag = sprintf('<%s> %s </%s>', $element->element, $element->value, $element->element);
            $elements[$element->element] = $tag;
        }

        return $elements;
    }
}
