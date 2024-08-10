<?php

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

    public function __construct(
        LoggerInterface $avitoBoardLogger,
        private readonly AppCacheInterface $cache,
    ) {
        $this->logger = $avitoBoardLogger;
    }

    /**
     * @return array{'model': array{string, int}, 'band': string, 'model': string }
     */
    public function getModel(string $nameInfo): ?array
    {
        $cache = $this->cache->init('avito-board');

        $brands = $cache->get('avito-board-model-' . $nameInfo, function (ItemInterface $item): array {

            $item->expiresAfter(3600);

            $UserAgentGenerator = new UserAgentGenerator();
            $userAgent = $UserAgentGenerator->genDesktop();

            $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
                ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

            $request = $httpClient->request('GET', 'format/brendy_fashion.xml');

            $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

            $json = json_encode($xml);

            return json_decode($json, true);
        });

        $string = mb_strtolower($nameInfo);

        $searchArray = explode(" ", $string);

        $result = [];

        foreach ($brands['brand'] as $brand)
        {

            // Получаем название бренда в Авито
            $brandName = trim(strtok($brand['@attributes']['name'], " "));

            if (in_array(mb_strtolower($brandName), $searchArray, false))
            {
                // Присваиваем в результирующий массив бренд, т.к. футболка может быть без модели
                $result['brand'] = $brand['@attributes']['name'];

                foreach ($brand['model_dlya_tipa_tovara'] as $models)
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
                        $result['model_dlya_tipa_tovara'][$models['@attributes']['name']] = $count;
                    }
                }
            }
        }

        if (isset($result['model_dlya_tipa_tovara']))
        {
            $maxValue = max($result['model_dlya_tipa_tovara']);
            $result['model_dlya_tipa_tovara'] = array_search($maxValue, $result['model_dlya_tipa_tovara'], true);
        }

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
}
