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
     */
    public function getModel(string $nameInfo): ?array
    {
        $this->nameInfo = $nameInfo;

        $cache = $this->cache->init('avito-board');

        $array = $cache->get('avito-board-model-' . $this->nameInfo, function (ItemInterface $item): array {

            $item->expiresAfter(3600);

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

        foreach ($array['make'] as $make)
        {
            $brandName = trim(strtok($make['@attributes']['name'], " "));

            if (in_array(mb_strtolower($brandName), $searchArray, false))
            {

                // удаляем название бренда из массива для поиска
                $unset = array_search(mb_strtolower($brandName), $searchArray);
                unset($searchArray[$unset]);

                foreach ($make['model'] as $models)
                {
                    $count = 0;

                    foreach ($searchArray as $in)
                    {
                        $modelName = $models['@attributes']['name'] ?? $models['name'];
                        $modelNameLower = mb_strtolower($modelName);

                        $isset = mb_substr_count($modelNameLower, $in);

                        // Определяем все элементы моделей, которые могут соответствовать поиску
                        if ($isset !== 0)
                        {
                            $count++; // увеличиваем вес

                            $searchModel = explode(' ', $modelNameLower);

                            // проверяем соответствие модели строке поиска
                            foreach ($searchModel as $confirm)
                            {
                                if (stripos($string, $confirm) === false)
                                {
                                    $count--; // снимаем вес если не соответствует
                                }
                            }
                        }

                        // пробуем удалить в строке символы «-»
                        if ($isset === 0)
                        {
                            $ins = str_replace('-', '', $in);

                            $isset = mb_substr_count($modelNameLower, $ins);

                            if ($isset !== 0)
                            {
                                $count++; // увеличиваем вес
                            }
                        }

                        // пробуем заменить в строке символы «-» на пробел
                        if ($isset === 0)
                        {
                            $ins = str_replace('-', ' ', $in);

                            $isset = mb_substr_count($modelNameLower, $ins);

                            if ($isset !== 0)
                            {
                                $count++; // увеличиваем вес
                            }
                        }

                        if ($isset === 0)
                        {
                            $ins = str_replace('-', '/', $in);

                            $isset = mb_substr_count($modelNameLower, $ins);

                            if ($isset !== 0)
                            {
                                $count++; // увеличиваем вес
                            }
                        }
                    }

                    if ($count > 0)
                    {
                        $result['models'][$models['@attributes']['name']] = $count;
                    }
                }

                if (isset($result['models']))
                {
                    // Присваиваем в массив название бренда если найдена модель
                    $result['brand'] = $make['@attributes']['name'];
                    break;
                }
            }
        }

        if (isset($result['models']))
        {
            $maxValue = max($result['models']);
            $result['model'] = array_search($maxValue, $result['models'], true);
        }

        // если модель не найдена - возвращаем null
        if (empty($result))
        {
            $this->logger->critical(
                'Не найдено совпадений бренда или модели для продукта ' . $nameInfo,
                [__FILE__ . ':' . __LINE__]
            );

            return null;
        }

        return $result;
    }

    /** @deprecated */
    private function xmlTransform(string $content): array
    {
        //         @TODO работать сначала с объектом, а как только наши нужную категорию - делать декодирование
        //        $xml = new \SimpleXMLElement($content);
        //
        //        foreach ($xml->make as $category)
        //        {
        //            dd($category['name']);
        //            dump($category);
        //            dump($category->model['name']);
        //            dump(json_decode(json_encode($category), true));
        //        }
        //        dd();
        $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $array = json_decode($json, true);

        $models = null;
        foreach ($array['make'] as $item)
        {
            foreach ($item['model'] as $model)
            {
                if (array_key_exists('name', $model))
                {
                    $models[$item['@attributes']['name']][] = $model['name'];
                }
                elseif (array_key_exists('@attributes', $model))
                {
                    $models[$item['@attributes']['name']][] = $model['@attributes']['name'];
                }
                else
                {
                    throw new \Exception('Ошибка парсинга');
                }
            }
        }

        // получаем массив, где ключ - название бреда шины, значение - массив с моделями шин данного бренда
        return $models;
    }

    /** @deprecated */
    private function search(): ?string
    {
        $cache = $this->cache->init('avito-board');
        $cachePool = $cache->getItem('avito-board-models-' . $this->nameInfo);

        if (false === $cachePool->isHit())
        {
            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
                ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

            $request = $httpClient->request('GET', 'format/tyres_make.xml');

            $cachePool->expiresAfter(DateInterval::createFromDateString('3600 minutes'));
            $cachePool->set($this->xmlTransform($request->getContent()));
            $cache->save($cachePool);
        }

        $brands = $cachePool->get() ?? throw new \DomainException(message: 'Ошибка');

        $nameInfo = explode(' ', $this->nameInfo);

        $models = null;
        foreach ($nameInfo as $info)
        {
            if (in_array($info, $brands))
            {

                $models = $brands[$info];
                unset($nameInfo[$info]);
            }
        }

        $search = null;
        $count = 0;
        foreach ($models as $model)
        {
            foreach ($nameInfo as $info)
            {
                if (substr_count($model, $info) > 0)
                {
                    $search[$model] = ++$count;
                }
            }
        }

        if (null === $search)
        {
            $this->logger->critical(
                'Не найдено совпадений для модели ' . implode(' ', $nameInfo),
                [__FILE__ . ':' . __LINE__]
            );

            return null;
        }

        // @TODO по какому принципу выбирать модель, если количество вхождений одинаковое?
        return array_search(max($search), $search);
    }
}
