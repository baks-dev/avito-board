<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
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
    private SymfonyStyle $io;

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
