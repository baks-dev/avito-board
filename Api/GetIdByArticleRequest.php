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

declare(strict_types=1);

namespace BaksDev\Avito\Board\Api;

use BaksDev\Avito\Api\AvitoApi;
use DateInterval;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Cache\ItemInterface;

#[Autoconfigure(public: true)]
final class GetIdByArticleRequest extends AvitoApi
{
    /**
     * Метод позволяет получить идентификаторы (ID) объявлений на Авито по идентификаторам объявлений из файла автозагрузки.
     * Возвращает FALSE - в случае если идентификатор объявления Авито по артикулу не найден
     *
     * @see https://developers.avito.ru/api-catalog/autoload/documentation#operation/getAvitoIdsByAdIds
     */
    public function find(string $article): int|false
    {
        $cache = $this->getCacheInit('avito-promotion');

        $key = md5($this->getTokenIdentifier().'.'.$article);
        //$cache->delete($key);

        $id = $cache->get($key, function(ItemInterface $item) use ($article): int|false {

            /** По умолчанию кешируем на 1 сек на случай, если результат вернет FALSE */
            $item->expiresAfter(DateInterval::createFromDateString('1 second'));

            $request = $this->tokenHttpClient()->request(
                'GET',
                '/autoload/v2/items/avito_ids',
                ['query' => ['query' => $article]]
            );

            if($request->getStatusCode() !== 200)
            {
                return false;
            }

            $content = $request->toArray();

            if(
                false === isset($content['items']) ||
                false === is_array($content['items'])
            )
            {
                return false;
            }

            $current = current($content['items']);

            if(false === isset($current['avito_id']))
            {
                return false;
            }

            /** Если результат вернул идентификатор - кешируем на сутки */
            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $current['avito_id'];

        });

        return $id;
    }
}
