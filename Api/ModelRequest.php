<?php

namespace BaksDev\Avito\Board\Api;

use BaksDev\Core\Type\UserAgent\UserAgentGenerator;
use SimpleXMLElement;
use Symfony\Component\HttpClient\HttpClient;

final readonly class ModelRequest
{
    public function getModel(string $productName)
    {
        $product = explode(' ', $productName);
        $brand = $product[0];
        unset($product[0]);
        $info = new \ArrayObject($product);
        dump($product);

        $UserAgentGenerator = new UserAgentGenerator();
        $userAgent = $UserAgentGenerator->genDesktop();

        $httpClient = HttpClient::create(['headers' => ['User-Agent' => $userAgent]])
            ->withOptions(['base_uri' => 'https://autoload.avito.ru']);

        $request = $httpClient->request('GET', 'format/tyres_make.xml');


        $xml = simplexml_load_string($request->getContent(), "SimpleXMLElement", LIBXML_NOCDATA);

        $brands = $this->xmlTransform($request->getContent(), $brand);

        foreach ($brands[$brand] as $model)
        {
            $iterator = $info->getIterator();

//            while (substr_count($model, $iterator->current()) > 0) {
//                $iterator->next();
//            }

            dd();

//            foreach ($product as $info)
//            {
//                if (substr_count($model, $info) > 0)
//                {
//                    dump($model);
//
//                }
//            }
        }

        dd();

        $json = json_encode($xml);
        $array = json_decode($json, true);
        dump($xml);
        dump($array);
        dump($xml);

        //        foreach ($xml->make as $category)
        //        {
        //            if ($category['name'] == $brand)
        //            {
        //
        //                dump($category);
        //                dump($category->model['name']);
        //                dump(json_decode(json_encode($category), true));
        //
        //            }
        //        }

        $brands = null;

        foreach ($array['make'] as $item)
        {
            if ($item['@attributes']['name'] == $brand)
            {
                foreach ($item['model'] as $model)
                {
                    dump($model['@attributes']['name']);
                }
                //                foreach ($product as $info) {
                //                    dd($info);
                //                }
            }
        }
        dd();
    }

    private function xmlTransform(string $content, string $brand): array
    {
        $xml = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOCDATA);

        $json = json_encode($xml);
        $array = json_decode($json, true);


        $brands = null;
        foreach ($array['make'] as $item)
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

        return $brands;
    }

}
