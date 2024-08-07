<?php

namespace BaksDev\Avito\Board\Api;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Type\UserAgent\UserAgentGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;

final class TireModelRequest
{
    private string $category;

    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $avitoLogger,
        private readonly AppCacheInterface $cache,
    ) {
        $this->logger = $avitoLogger;
    }

    public function getModel(string $category, array $nameInfo): ?string
    {
        $this->category = $category;

        $cache = $this->cache->init('avito-board', 86400);

        $cachePool = $cache->getItem('avito-board-models-' . $this->category);

        if (false === $cachePool->isHit())
        {
            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
                ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

            $request = $httpClient->request('GET', 'format/tyres_make.xml');

            $cachePool->set($this->xmlTransform($request->getContent()));
            $cache->save($cachePool);
        }

        $brands = $cachePool->get() ?? throw new \DomainException(message: 'Ошибка');

        $search = null;
        foreach ($brands[$category] as $model)
        {
            $count = 0;
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

    private function xmlTransform(string $content): array
    {
        // @TODO работать сначала с объектом, а как только наши нужную категорию - делать декодирование
        // $xml = new \SimpleXMLElement($request->getContent());
        //
        //        foreach ($xml->make as $category)
        //        {
        //            if ($category['name'] == $brand)
        //            {
        //                dump($category);
        //                dump($category->model['name']);
        //                dump(json_decode(json_encode($category), true));
        //            }
        //        }
        //        dd();

        $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $array = json_decode($json, true);

        $brands = null;
        foreach ($array['make'] as $item)
        {
            if ($item['@attributes']['name'] === $this->category)
            {
                foreach ($item['model'] as $model)
                {
                    if (array_key_exists('name', $model))
                    {
                        $brands[$item['@attributes']['name']][] = $model['name'];
                    }
                    elseif (array_key_exists('@attributes', $model))
                    {
                        $brands[$item['@attributes']['name']][] = $model['@attributes']['name'];
                    }
                    else
                    {
                        throw new \Exception('Ошибка парсинга');
                    }
                }
            }
        }

        return $brands;
    }
}
