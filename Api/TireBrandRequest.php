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

final class TireBrandRequest
{
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


    /**
     * @return array{'models': array{string, int}, 'band': string, 'model': string }|null
     * @see https://autoload.avito.ru/format/tyres_make.xml
     */
    public function find(): string|null
    {
        if(empty($this->brand))
        {
            throw new InvalidArgumentException('Invalid Argument Brand');
        }


        $cache = $this->cache->init("avito-board");

        /** Получаем весь документ */
        //$cache->deleteItem("avito-board-tires");

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


        $key = "avito-board-brand-".md5($this->brand);
        //$cache->deleteItem($key);

        $result = $cache->get($key, function(ItemInterface $item) use ($document): string|false {

            $item->expiresAfter(DateInterval::createFromDateString("10 seconds"));

            $document = json_decode($document, true, 512, JSON_THROW_ON_ERROR);

            // собираем карту только по брендам
            $brandMap = [];

            foreach($document['make'] as $i)
            {
                $name = $i['@attributes']['name'] ?? null;

                if(is_string($name))
                {
                    $brandMap[$name] = 0;
                }
            }

            if(empty($brandMap))
            {
                return false;
            }


            // Разбиваем исходную строку на массив слов
            $words = explode(' ', $this->brand);

            foreach($brandMap as $key => $value)
            {
                foreach($words as $currentAttempt)
                {
                    $search = mb_strtolower($currentAttempt, 'UTF-8');

                    if(str_contains(mb_strtolower($key, 'UTF-8'), $search))
                    {
                        ++$brandMap[$key];
                    }
                }
            }

            $maxValue = max($brandMap);

            /** Если найдено значение с весом больше 0 */
            if($maxValue > 0)
            {
                $item->expiresAfter(DateInterval::createFromDateString("1 day"));

                /** Находим все результаты */
                $maxKeys = array_keys($brandMap, $maxValue);

                /** Если значений больше одного - проверяем обратное вхождение */
                if(count($maxKeys) > 1)
                {
                    foreach($maxKeys as $exist)
                    {
                        if(str_contains(mb_strtolower($this->brand, 'UTF-8'), mb_strtolower($exist, 'UTF-8')))
                        {
                            return $exist;
                        }
                    }
                }

                return current($maxKeys);
            }

            return false;
        });

        return $result;
    }
}
