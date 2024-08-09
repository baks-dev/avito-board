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
use Psr\Log\LoggerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class ProductTransformerExtension extends AbstractExtension
{
    private ?string $avitoCategory = null;

    private ?string $mapper = null;

    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $avitoBoardLogger,
        private readonly AvitoBoardMapperProvider $mapperProvider,
    ) {
        $this->logger = $avitoBoardLogger;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('product_transform', [$this, 'productTransform']),
        ];
    }

    public function productTransform(array $product): ?array
    {
        /** Возвращаем null, если нет маппера для продукта */
        if ($product['avito_board_avito_category'] === null)
        {
            return null;
        }

        $this->avitoCategory = $product['avito_board_avito_category'];
        $this->mapper = $product['avito_board_mapper'];

        /** Получаем элементы по продукту, НЕ УЧАСТВУЮЩИЕ в маппинге */
        $unmappedElements = array_filter(
            $this->mapperProvider->filterElements($this->avitoCategory),
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
                $data = $element->fetchData($product);

                // @TODO если у продукта есть свойство null, обязательное для Авито - пропускаем продукт, пишем в лог
                if ($data === null && $element->isRequired())
                {
                    $this->logger->critical(
                        sprintf(
                            'В свойства продукта не найдено значение для обязательного элемента Авито! Название продукта: %s Название элемента: %s',
                            $product['product_name'],
                            $element->element()
                        ),
                        [__FILE__ . ':' . __LINE__]
                    );

                    return null;
                }

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
        $mappedElements = $this->getElements();

        /**
         * Объединяем массивы элементов по принципу:
         * - элемент, описанный в классе имеет приоритет над элементом, полученным из маппера
         *  (элемент класса перезаписывает элемент из маппера)
         */
        $allElements = array_merge($mappedElements, $elements);

        /** Убираем значение равные null */
        $feedElements = array_filter($allElements, function (?string $value) {
            return $value !== null;
        });

        return $feedElements;
    }

    private function getElements(): array
    {
        $mapper = $this->mapperTransform();

        array_walk($mapper, function (&$value, $element) use ($mapper) {
            $instance = $this->mapperProvider->getElement($this->avitoCategory, $element);

            $value = $instance->fetchData($mapper);
        });

        return $mapper;
    }

    private function mapperTransform(): array
    {
        $transform = null;
        foreach (json_decode($this->mapper, false, 512, JSON_THROW_ON_ERROR) as $element)
        {
            $transform[$element->element] = $element->value;
        }

        return $transform;
    }
}
