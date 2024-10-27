<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Board\Messenger\Schedules;

use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Twig\TemplateExtension;
use DateInterval;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Twig\Environment;

#[AsMessageHandler]
final readonly class FeedCacheRefreshHandler
{
    protected LoggerInterface $logger;

    public function __construct(
        LoggerInterface $avitoBoardLogger,
        private AppCacheInterface $cache,
        private Environment $environment,
        private TemplateExtension $templateExtension,
        private AllProductsWithMapperInterface $allProductsWithMapping,
    )
    {
        $this->logger = $avitoBoardLogger;
    }

    public function __invoke(FeedCacheRefreshMessage $message): void
    {
        $profile = $message->getProfile();

        $products = $this->allProductsWithMapping
            ->profile($profile)
            ->execute();

        /** Если продукты не найдены - прерываем хендлер, пишем в лог */
        if(empty($products))
        {
            $this->logger->warning('Продукты не найдены', [__FILE__.':'.__LINE__]);

            return;
        }

        $cache = $this->cache->init('avito-board');

        $cachePool = $cache->getItem('feed-'.$profile);

        $template = $this->templateExtension->extends('@avito-board:public/export/feed/export.html.twig');

        $feed = $this->environment->render($template, ['products' => $products]);

        $cachePool->expiresAfter(DateInterval::createFromDateString('1 day'));

        $cachePool->set($feed);
        $cache->delete('feed-'.$profile);
        $cache->save($cachePool);
    }
}
