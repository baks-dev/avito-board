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
     * @return array{'band': string, 'cached': bool }
     *     |array{'model_matches': array{string, int}, 'band': string, 'model': string, 'cached': bool }
     *     |null
     */
    public function getModel(string $nameInfo): ?array
    {
        $cached = true;
        $cache = $this->cache->init('avito-board');

        $brands = $cache->get('avito-board-model-' . $nameInfo, function (ItemInterface $item) use (&$cached): array {
            $cached = false;

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

        $nameParts = explode(" ", $string);

        $result = null;

        //        foreach ($brands['brand'] as $brand)
        //        {
        //            // Получаем название бренда в Авито
        //            $avitoBrand = trim(strtok($brand['@attributes']['name'], " "));
        //
        //            if (in_array(mb_strtolower($avitoBrand), $nameParts, false))
        //            {
        //
        //                if (array_key_exists('model_dlya_tipa_tovara', $brand))
        //                {
        //                    dump($brand['@attributes']['name']);
        //                    dump($brand['model_dlya_tipa_tovara']);
        //                }
        //                else
        //                {
        //                    dump($brand['@attributes']['name']);
        //
        //                }
        //            }
        //        }
        //                dd();

        foreach ($brands['brand'] as $brand)
        {
            // Получаем название бренда в Авито
            $avitoBrand = trim(strtok($brand['@attributes']['name'], " "));

            if (in_array(mb_strtolower($avitoBrand), $nameParts, false))
            {
                // удаляем название бренда из массива для поиска
                //                $unset = array_search(mb_strtolower($avitoBrand), $nameParts);
                //                unset($nameParts[$unset]);

                if (array_key_exists('model_dlya_tipa_tovara', $brand) === false)
                {
                    // Присваиваем в результирующий массив бренд, т.к. футболка может быть без модели
                    $result['brand'] = $brand['@attributes']['name'];
                    break;
                }

                // Определяем, есть ли у бренда список моделей
                if (array_key_exists('model_dlya_tipa_tovara', $brand) === true)
                {
                    // Присваиваем в результирующий массив бренд, т.к. футболка может быть без модели
                    $result['brand'] = $brand['@attributes']['name'];

                    // Получаем массив моделей
                    foreach ($brand['model_dlya_tipa_tovara'] as $model)
                    {
                        $count = 0;

                        foreach ($nameParts as $namePart)
                        {
                            $avitoModel = mb_strtolower($model['@attributes']['name']);

                            $isset = mb_substr_count($avitoModel, $namePart);

                            // Если вхождение найдено - определяем все элементы моделей, которые могут соответствовать поиску
                            if ($isset !== 0)
                            {
                                $count++; // увеличиваем вес

                                $avitoModelParts = explode(' ', $avitoModel);

                                // проверяем соответствие модели строке поиска
                                foreach ($avitoModelParts as $avitoModelPart)
                                {
                                    if (stripos($string, $avitoModelPart) === false)
                                    {
                                        $count--; // снимаем вес если не соответствует
                                    }
                                }
                            }

                            // пробуем удалить в строке символы «-»
                            if ($isset === 0)
                            {
                                $ins = str_replace('-', '', $namePart);

                                $isset = mb_substr_count($avitoModel, $ins);

                                if ($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }

                            // пробуем заменить в строке символы «-» на пробел
                            if ($isset === 0)
                            {
                                $ins = str_replace('-', ' ', $namePart);

                                $isset = mb_substr_count($avitoModel, $ins);

                                if ($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }

                            if ($isset === 0)
                            {
                                $ins = str_replace('-', '/', $namePart);

                                $isset = mb_substr_count($avitoModel, $ins);

                                if ($isset !== 0)
                                {
                                    $count++; // увеличиваем вес
                                }
                            }
                        }


                        if ($count > 0)
                        {
                            $result['model_matches'][$model['@attributes']['name']] = $count;
                        }
                        else
                        {
                            $result['model'] = 'Другая';
                        }
                    }

                    if (isset($result['model_matches']))
                    {
                        // Если модель найдена - перезаписываем брендом, к которому принадлежит модель
                        $result['brand'] = $brand['@attributes']['name'];
                        break;
                    }
                }
            }
        }

        if (null === $result)
        {
            $this->logger->critical(
                'Не найдено совпадений бренда или модели для продукта ' . $nameInfo,
                [__FILE__ . ':' . __LINE__]
            );

            return null;
        }

        if (isset($result['model_matches']))
        {
            $maxValue = max($result['model_matches']);

            $result['model'] = array_search($maxValue, $result['model_matches'], true);
        }

        $result['cached'] = $cached;

        return $result;
    }
}
