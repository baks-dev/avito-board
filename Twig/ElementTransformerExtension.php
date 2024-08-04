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
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardExtendElementInterface;
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
        ];
    }

    public function elementTransform(array $product): ?array
    {
        /** Возвращаем null, если нет маппера для продукта */
        if ($product['avito_board_avito_category'] === null)
        {
            return null;
        }

        /** Получаем элементы по продукту, НЕ УЧАСТВУЮЩИЕ в маппинге */
        $unmappedElements = array_filter(
            $this->mapperProvider->filterElements($product['avito_board_avito_category']),
            function (AvitoBoardElementInterface $element) {
                return $element->isMapping() === false;
            }
        );

        /**
         * Формируем массив для отрисовки в фиде, где ключ - название элемента, значение - значением из свойств продукта
         */
        $elements = null;
        foreach ($unmappedElements as $element)
        {
            if ($element->getDefault() === null)
            {
                $element->setData($product);

                $data = $element->fetchData();

                if ($data === null)
                {
                    // @TODO если значение свойства продукта null - пропускать элемент, не добавлять в фид
                    continue;
                }

                $elements[$element->element()] = $data;
            }
            else
            {
                $elements[$element->element()] = $element->getDefault();
            }
        }

        /** Преобразуем строку маппера в массив элементов */
        $mappedElements = $this->mapperTransform($product['avito_board_mapper'], $product['avito_board_avito_category']);

        /**
         * Объединяем массивы элементов по принципу:
         * - элемент, описанный в классе имеет приоритет над элементом, полученным из маппера
         *  (элемент класса перезаписывает элемент из маппера)
         */
        $allElements = array_merge($mappedElements, $elements);

        return $allElements;
    }

    private function mapperTransform(string $mapper, string $category): array
    {
        $mapper = json_decode($mapper, false, 512, JSON_THROW_ON_ERROR);

        $instances = $this->getInstances($mapper, $category);

        return $this->getElements($instances);
    }

    /**
     * Получаем массив, где ключ - название класса элемента, значение - инстанс класса элемента
     *
     * @return array<class-string, AvitoBoardElementInterface>
     */
    private function getInstances(array $mapper, string $category): array
    {
        $instances = null;
        foreach ($mapper as $element)
        {
            $instance = $this->mapperProvider->getOneElement($category, $element->element);
            $instance->setData($element->value);
            $instances[$instance::class] = $instance;
        }

        return $instances;
    }

    /**
     * Формируем массив для отрисовки в фиде, где ключ - название элемента, значение - значением из свойств продукта
     * /
     * @param array<class-string, AvitoBoardElementInterface> $instances
     * @return array<string, string>
     */
    private function getElements(array $instances): array
    {
        $elements = null;

        foreach ($instances as $instance)
        {
            $baseClass = get_parent_class($instance);

            if ($baseClass)
            {
                /** @var AvitoBoardExtendElementInterface $extend */
                $extend = $instance;
                $base = $instances[$baseClass];

                $extend->setBaseData($base);

                $elements[$base->element()] = $extend->fetchData();
            }
            else
            {
                if(isset($elements[$instance->element()]))
                {
                    continue;
                }

                $elements[$instance->element()] = $instance->fetchData();
            }
        }

        return $elements;
    }
}
