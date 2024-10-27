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

namespace BaksDev\Avito\Board\Api;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Type\UserAgent\UserAgentGenerator;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

final class ShirtModelRequest
{
    protected LoggerInterface $logger;

    // флаг для отслеживания кеширования
    private bool $cached = true;

    public function __construct(
        LoggerInterface $avitoBoardLogger,
        private readonly AppCacheInterface $cache,
    )
    {
        $this->logger = $avitoBoardLogger;
    }

    /**
     * @return array{'band': string, 'model': null, 'cached': bool }
     * @return array{'band': string, 'model': string, 'cached': bool, 'model_matches': array{string, int} }
     * @return null
     *
     * @see https://autoload.avito.ru/format/brendy_fashion.xml
     */
    public function getModel(string $productName): ?array
    {

        $cache = $this->cache->init('avito-board');

        $brands = $cache->get(
            'avito-board-model-'.md5($productName),
            function(ItemInterface $item): array {

                $this->cached = false;

                $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                $UserAgentGenerator = new UserAgentGenerator();
                $userAgent = $UserAgentGenerator->genDesktop();

                $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
                    ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

                $request = $httpClient->request('GET', 'format/brendy_fashion.xml');

                $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

                $json = json_encode($xml);

                return json_decode($json, true);
            }
        );

        $productNameLower = mb_strtolower($productName);

        $productNameParts = explode(" ", $productNameLower);

        $search = null;

        $brandSearch = null;

        foreach($brands['brand'] as $brand)
        {
            // Получаем название бренда в Авито и разбиваем на токены
            $avitoBrandToken = trim(strtok($brand['@attributes']['name'], " "));

            // Ищем токены в строке с названием продукта
            if(in_array(mb_strtolower($avitoBrandToken), $productNameParts, false))
            {

                // В найденном бренде Авито проверяем наличие массива с моделями
                if(array_key_exists('model_dlya_tipa_tovara', $brand) === false)
                {
                    // Присваиваем в результирующий массив бренд. Так как бренд без модели, model = NULL (футболка может быть без модели)
                    $search['brand'] = $brand['@attributes']['name'];
                    $search['model'] = null;
                    break;
                }

                // Если у бренда есть модели
                if(array_key_exists('model_dlya_tipa_tovara', $brand))
                {
                    // Присваиваем в результирующий массив бренд, т.к. футболка может быть без модели
                    $search['brand'] = $brand['@attributes']['name'];

                    // Ранжирование всех одинаковых брендов с моделями
                    $avitoBrandLow = mb_strtolower($brand['@attributes']['name']);
                    $avitoBrandParts = explode(' ', $avitoBrandLow);

                    $brandCount = 0;
                    foreach($avitoBrandParts as $avitoBrandToken)
                    {
                        $match = mb_substr_count($avitoBrandLow, $avitoBrandToken);

                        if($match !== 0)
                        {
                            $brandCount++;
                            $brandSearch['brand_matches'][$brand['@attributes']['name']] = $brandCount;
                            $search['brand_matches'][$brand['@attributes']['name']] = $brandCount;
                        }
                    }

                    // Цикл для поиска совпадения модели
                    foreach($brand['model_dlya_tipa_tovara'] as $model)
                    {
                        $count = 0;

                        foreach($productNameParts as $namePart)
                        {
                            $avitoModel = mb_strtolower($model['@attributes']['name']);

                            $isset = mb_substr_count($avitoModel, $namePart);

                            // Если вхождение найдено - определяем все элементы моделей, которые могут соответствовать поиску
                            if($isset !== 0)
                            {
                                $count++; // увеличиваем вес

                                $avitoModelParts = explode(' ', $avitoModel);

                                // проверяем соответствие модели строке поиска
                                foreach($avitoModelParts as $avitoModelPart)
                                {
                                    if(stripos($productNameLower, $avitoModelPart) === false)
                                    {
                                        $count--; // снимаем вес если не соответствует
                                    }
                                }
                            }

                            // пробуем удалить в строке символы «-»
                            if($isset === 0)
                            {
                                $ins = str_replace('-', '', $namePart);

                                $isset = mb_substr_count($avitoModel, $ins);

                                if($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }

                            // пробуем заменить в строке символы «-» на пробел
                            if($isset === 0)
                            {
                                $ins = str_replace('-', ' ', $namePart);

                                $isset = mb_substr_count($avitoModel, $ins);

                                if($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }

                            if($isset === 0)
                            {
                                $ins = str_replace('-', '/', $namePart);

                                $isset = mb_substr_count($avitoModel, $ins);

                                if($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }
                        }

                        if($count > 0)
                        {
                            $search['model_matches'][$model['@attributes']['name']] = $count;
                        }
                        else
                        {
                            $search['model'] = 'Другая';
                        }
                    }

                    if(isset($search['model_matches']))
                    {
                        // Если модель найдена - перезаписываем брендом, к которому принадлежит модель
                        $search['brand'] = $brand['@attributes']['name'];
                        break;
                    }
                }
            }
        }

        // Если результатов поиска нет - нет обязательного
        if(null === $search)
        {
            $this->logger->critical(
                'Не найдено совпадений бренда или модели для продукта '.$productName,
                [__FILE__.':'.__LINE__]
            );

            return null;
        }

        // если было несколько одинаковых брендов
        if($brandSearch !== null)
        {
            $min = min($brandSearch['brand_matches']);

            $search['brand'] = array_search($min, $brandSearch['brand_matches'], true);
            $search['model'] = 'Другая';
        }

        if(isset($search['model_matches']))
        {
            $maxValue = max($search['model_matches']);

            $search['model'] = array_search($maxValue, $search['model_matches'], true);
        }

        $search['cached'] = $this->cached;

        return $search;
    }
}
