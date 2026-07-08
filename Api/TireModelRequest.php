<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

final class TireModelRequest
{
    private string|false $model = false;

    private string|false $brand = false;

    public function __construct(
        #[Target("avitoBoardLogger")] private LoggerInterface $logger,
        private AppCacheInterface $cache,
    ) {}

    public function brand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function model(string $model): self
    {
        /** Нормализация строки */

        // Приводим в нижний регистр и разбивка на слова
        $searchWords = preg_split('/[\s,\.\-]+/', mb_strtolower($model));
        // Удаляем слишком короткие слова (например, "e", "a")
        $searchWords = array_filter($searchWords, static fn($word) => mb_strlen($word) > 1);

        $this->model = implode(' ', $searchWords);

        return $this;
    }

    /**
     * @return array{'models': array{string, int}, 'band': string, 'model': string }|null
     * @see https://autoload.avito.ru/format/tyres_make.xml
     */
    public function find(): string|false
    {
        if(empty($this->brand))
        {
            throw new InvalidArgumentException('Invalid Argument Brand');

        }

        if(empty($this->model))
        {
            throw new InvalidArgumentException('Invalid Argument Model');
        }


        $cache = $this->cache->init("avito-board");

        /** Получаем весь документ */
        //$cache->deleteItem('avito-board-tires');

        $document = $cache->get("avito-board-tires", function(ItemInterface $item): string|false {

            $item->expiresAfter(DateInterval::createFromDateString("10 second"));

            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClient = HttpClient::create(["headers" => ["User-Agent" => $userAgent]])
                ->withOptions(["base_uri" => "https://autoload.avito.ru"]);

            $request = $httpClient->request(
                "GET",
                "format/tyres_make.xml",
            );

            if($request->getStatusCode() !== 200)
            {
                return false;
            }

            $item->expiresAfter(DateInterval::createFromDateString("1 day"));

            $xml = simplexml_load_string(
                $request->getContent(),
                "SimpleXMLElement",
                LIBXML_NOCDATA,
            );

            return json_encode($xml);
        });

        if(empty($document))
        {
            return false;
        }


        $modelMapAllKey = "avito-board-all-model-".md5($this->model);
        // $cache->deleteItem($modelMapAllKey);

        $modelMapSearch = $cache->get($modelMapAllKey, function(ItemInterface $item) use ($document): string|false {

            $item->expiresAfter(DateInterval::createFromDateString("10 second"));

            $document = json_decode($document, true, 512, JSON_THROW_ON_ERROR);

            $modelMapAll = [];

            // Ищем нужный бренд и собираем карту моделей
            foreach($document['make'] as $i)
            {
                if(($i['@attributes']['name'] ?? '') === $this->brand)
                {
                    $models = $i['model'] ?? [];

                    // Защита: если модель всего одна, PHP/XML-парсер часто делает массив ассоциативным,
                    // а не списком. Принудительно превращаем в список для единого цикла.
                    if(isset($models['@attributes']))
                    {
                        $models = [$models];
                    }

                    // Быстро собираем хэш-карту моделей для выбранного бренда
                    foreach($models as $modelItem)
                    {
                        $modelName = $modelItem['@attributes']['name'] ?? null;
                        if(is_string($modelName))
                        {
                            $modelMapAll[$modelName] = 0;
                        }
                    }

                    break; // Бренд найден, модели собраны — выходим из внешнего цикла
                }
            }

            if(empty($modelMapAll))
            {
                return false;
            }


            $words = explode(' ', $this->model);

            $item->expiresAfter(DateInterval::createFromDateString("10 second"));

            foreach($modelMapAll as $key => $value)
            {
                foreach($words as $currentAttempt)
                {
                    $search = mb_strtolower($currentAttempt, 'UTF-8');

                    if(str_contains(mb_strtolower($key, 'UTF-8'), $search))
                    {
                        ++$modelMapAll[$key];
                    }
                }
            }

            $maxValue = max($modelMapAll);

            /** Если найдено значение с весом больше 0 - возвращаем его */
            if($maxValue > 0)
            {
                $item->expiresAfter(DateInterval::createFromDateString("1 day"));

                /** Находим все результаты */
                $maxKeys = array_keys($modelMapAll, $maxValue);

                /** Если значений больше одного - проверяем обратное вхождение */
                if(count($maxKeys) > 1)
                {
                    foreach($maxKeys as $exist)
                    {
                        if(str_contains(mb_strtolower($this->model, 'UTF-8'), mb_strtolower($exist, 'UTF-8')))
                        {
                            return $exist;
                        }
                    }
                }

                return current($maxKeys);
            }

            return false;

        });

        if($modelMapSearch)
        {
            return $modelMapSearch;
        }


        /**
         * Пробуем определить по списку моделей для грузовиков и спецтехники
         */

        //$cache->deleteItem('avito-board-spec');

        $specDocument = $cache->get('avito-board-spec', function(ItemInterface $item): string|false {

            $item->expiresAfter(DateInterval::createFromDateString("10 second"));

            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClientSpec = HttpClient::create(["headers" => ["User-Agent" => $userAgent]])
                ->withOptions(["base_uri" => "https://avito.ru"]);

            $requestSpec = $httpClientSpec->request(
                "GET",
                "/web/1/autoload/user-docs/category/67021/field/121740/values-xml",
            );

            if($requestSpec->getStatusCode() !== 200)
            {
                return false;
            }

            $item->expiresAfter(DateInterval::createFromDateString("1 day"));

            $xml = simplexml_load_string(
                $requestSpec->getContent(),
                "SimpleXMLElement",
                LIBXML_NOCDATA,
            );

            return json_encode($xml);

        });

        if(empty($specDocument))
        {
            return false;
        }


        $modelMapAllKey = "avito-board-spec-model-".md5($this->model);
        //$cache->deleteItem($modelMapAllKey);

        $modelMapSearch = $cache->get($modelMapAllKey, function(ItemInterface $item) use ($specDocument): string|false {

            $item->expiresAfter(DateInterval::createFromDateString("10 second"));

            $arraySpec = json_decode($specDocument, true, 512, JSON_THROW_ON_ERROR);

            $flipped = array_flip($arraySpec['Model']);
            $modelMapSpec = array_fill_keys(array_keys($flipped), 0);

            if(empty($modelMapSpec))
            {
                return false;
            }

            $words = explode(' ', $this->model);

            $item->expiresAfter(DateInterval::createFromDateString("10 second"));

            foreach($arraySpec as $key => $value)
            {
                foreach($words as $currentAttempt)
                {
                    $search = mb_strtolower($currentAttempt, 'UTF-8');

                    if(str_contains(mb_strtolower($key, 'UTF-8'), $search))
                    {
                        ++$arraySpec[$key];
                    }
                }
            }

            $maxValue = max($arraySpec);

            /** Если найдено значение с весом больше 0 - возвращаем его */
            if($maxValue > 0)
            {
                $item->expiresAfter(DateInterval::createFromDateString("1 day"));

                /** Находим все результаты */
                $maxKeys = array_keys($arraySpec, $maxValue);

                /** Если значений больше одного - проверяем обратное вхождение */
                if(count($maxKeys) > 1)
                {
                    foreach($maxKeys as $exist)
                    {
                        if(str_contains(mb_strtolower($this->model, 'UTF-8'), mb_strtolower($exist, 'UTF-8')))
                        {
                            return $exist;
                        }
                    }
                }

                return current($maxKeys);
            }

            return false;

        });


        return $modelMapSearch;
    }
}
