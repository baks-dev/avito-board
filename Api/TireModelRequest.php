<?php

namespace BaksDev\Avito\Board\Api;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Type\UserAgent\UserAgentGenerator;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\Cache\ItemInterface;

final class TireModelRequest
{
    private string $nameInfo;

    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $avitoBoardLogger,
        private readonly AppCacheInterface $cache,
    ) {
        $this->logger = $avitoBoardLogger;
    }

    /**
     * @return array{'models': array{string, int}, 'band': string, 'model': string }|null
     * @see https://autoload.avito.ru/format/tyres_make.xml
     */
    public function getModel(string $nameInfo): ?array
    {
        $this->nameInfo = $nameInfo;

        $cache = $this->cache->init('avito-board');

        $array = $cache->get('avito-board-model-'.md5($this->nameInfo), function (ItemInterface $item): array {

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
                ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

            $request = $httpClient->request('GET', 'format/tyres_make.xml');

            $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

            $json = json_encode($xml);

            return json_decode($json, true);
        });

        $string = mb_strtolower($this->nameInfo);
        $searchArray = explode(" ", $string);

        $result = [];

        foreach($array['make'] as $make)
        {
            $brandName = trim(strtok($make['@attributes']['name'], " "));

            if(in_array(mb_strtolower($brandName), $searchArray, false))
            {

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
                        $result['models'][$models['@attributes']['name']] = $count;
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

        // если модель не найдена - возвращаем null
        if(empty($result))
        {
            $this->logger->critical(
                'Не найдено совпадений бренда или модели для продукта '.$nameInfo,
                [__FILE__.':'.__LINE__]
            );

            return null;
        }

        return $result;
    }
}
