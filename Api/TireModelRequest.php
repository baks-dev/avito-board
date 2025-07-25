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
 */

namespace BaksDev\Avito\Board\Api;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Type\UserAgent\UserAgentGenerator;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class TireModelRequest
{
    public function __construct(
        #[Target('avitoBoardLogger')] private LoggerInterface $logger,
        private AppCacheInterface $cache,
    ) {}

    /**
     * @return array{'models': array{string, int}, 'band': string, 'model': string }|null
     * @see https://autoload.avito.ru/format/tyres_make.xml
     */
    public function getModel(string $nameInfo): ?array
    {
        $cache = $this->cache->init('avito-board');
        $key = 'avito-board-'.md5($nameInfo);
        // $cache->delete($key);

        $result = $cache->get($key, function(ItemInterface $item) use ($nameInfo): array {

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
                ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

            $request = $httpClient->request('GET', 'format/tyres_make.xml');

            $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

            $json = json_encode($xml);
            $array = json_decode($json, true);

            // Форматированная строка модели без найденного бренда

            $formatModel['brand'] = $nameInfo;
            $formatModel['model'] = $nameInfo;

            $string = mb_strtolower($nameInfo);
            $searchArray = explode(" ", $string);

            $result = [];

            foreach($array['make'] as $make)
            {
                $brandName = trim(strtok($make['@attributes']['name'], " "));

                if(in_array(mb_strtolower($brandName), $searchArray, false))
                {

                    // Форматируем массив с брендом и моделью
                    $formatModel['model'] = trim(str_ireplace($brandName, '', $formatModel['brand']));
                    $formatModel['brand'] = $brandName;

                    // удаляем название бренда из массива для поиска
                    $unset = array_search(mb_strtolower($brandName), $searchArray);
                    unset($searchArray[$unset]);

                    foreach($make['model'] as $models)
                    {
                        $count = 0;

                        foreach($searchArray as $in)
                        {
                            $modelName = $models['@attributes']['name'] ?? $models['name'];
                            $modelNameLower = mb_strtolower($modelName);

                            $isset = mb_substr_count($modelNameLower, $in);

                            // Определяем все элементы моделей, которые могут соответствовать поиску
                            if($isset !== 0)
                            {
                                $count++; // увеличиваем вес

                                $searchModel = explode(' ', $modelNameLower);

                                // проверяем соответствие модели строке поиска
                                foreach($searchModel as $confirm)
                                {
                                    if(stripos($string, $confirm) === false)
                                    {
                                        $count--; // снимаем вес если не соответствует
                                    }
                                }
                            }

                            // пробуем удалить в строке символы «-»
                            if($isset === 0)
                            {
                                $ins = str_replace('-', '', $in);

                                $isset = mb_substr_count($modelNameLower, $ins);

                                if($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }

                            // пробуем заменить в строке символы «-» на пробел
                            if($isset === 0)
                            {
                                $ins = str_replace('-', ' ', $in);

                                $isset = mb_substr_count($modelNameLower, $ins);

                                if($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }

                            if($isset === 0)
                            {
                                $ins = str_replace('-', '/', $in);

                                $isset = mb_substr_count($modelNameLower, $ins);

                                if($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }
                        }

                        if($count > 0)
                        {
                            if(!isset($models['@attributes']) && isset($models['name']))
                            {
                                $result['models'][$models['name']] = $count;
                            }
                            else
                            {
                                $result['models'][$models['@attributes']['name']] = $count;
                            }
                        }
                    }

                    if(isset($result['models']))
                    {
                        // Присваиваем в массив название бренда если найдена модель
                        $result['brand'] = $make['@attributes']['name'];
                        break;
                    }
                }
            }

            if(isset($result['models']))
            {
                $maxValue = max($result['models']);
                $result['model'] = array_search($maxValue, $result['models'], true);
            }

            // если модель не найдена - возвращаем результат отформатированной строки
            if(empty($result))
            {
                $this->logger->critical(
                    sprintf('Не найдено совпадений бренда или модели для продукта %s. Присвоили значение из карточки', $nameInfo),
                    [self::class.':'.__LINE__, $formatModel],
                );

                $result = $formatModel;
            }

            return $result;

        });

        return $result;
    }
}
