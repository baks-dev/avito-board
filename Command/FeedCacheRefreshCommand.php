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

namespace BaksDev\Avito\Board\Command;

use BaksDev\Avito\Board\Messenger\Schedules\FeedCacheRefreshMessage;
use BaksDev\Avito\Repository\AllUserProfilesByActiveToken\AllUserProfilesByTokenRepository;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Обновляет кеш для фида
 */
#[AsCommand(
    name: 'baks:avito-board:feed:cache:refresh',
    description: 'Обновляет кеш для фида'
)]
class FeedCacheRefreshCommand extends Command
{
    public function __construct(
        private readonly AppCacheInterface $cache,
        private readonly MessageDispatchInterface $messageDispatch,
        private readonly AllUserProfilesByTokenRepository $allProfilesByToken,
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cache = $this->cache->init('avito-board');

        /** Получаем все активные профили, у которых активный токен */
        $profiles = $this->allProfilesByToken->findProfilesByActiveToken();

        $cached = null;

        if($profiles->valid())
        {
            foreach($profiles as $profile)
            {
                $key = 'feed-'.$profile;
                $this->messageDispatch->dispatch(new FeedCacheRefreshMessage($profile));
                $cacheItem = $cache->getItem($key);

                if(true === $cacheItem->isHit())
                {
                    $cached[] = (string) $profile;
                }
            }
        }

        if(null !== $cached)
        {
            $result = 'Кеширование фида выполнено успешно для следующих профилей:';

            foreach($cached as $profile)
            {
                $result .= ' | ID: '.$profile;
            }

            $output->writeln([$result]);

            return Command::SUCCESS;
        }

        $output->writeln('Профилей с активным токеном для Авито не найдено. Кеширование фида не выполнено');

        return Command::SUCCESS;
    }
}
