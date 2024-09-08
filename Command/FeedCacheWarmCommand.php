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

use BaksDev\Avito\Board\Messenger\Schedules\RefreshFeedMessage;
use BaksDev\Avito\Repository\AllUserProfilesByActiveToken\AllUserProfilesByTokenRepository;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Прогревает кеш для фида
 */
#[AsCommand(
    name: 'baks:avito-board:feed:cache:warm',
    description: 'Прогревает кеш для фида'
)]
class FeedCacheWarmCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AppCacheInterface $cache,
        private readonly MessageDispatchInterface $messageDispatch,
        private readonly AllUserProfilesByTokenRepository $allProfilesByToken,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cache = $this->cache->init('avito-board');

        /** Получаем все активные профили, у которых активный токен */
        $profiles = $this->allProfilesByToken->findProfilesByActiveToken();

        $cached = null;

        if ($profiles->valid())
        {
            foreach ($profiles as $profile)
            {
                $key = 'feed-' . $profile;
                $this->messageDispatch->dispatch(new RefreshFeedMessage($profile));
                $cacheItem = $cache->getItem($key);

                if (true === $cacheItem->isHit())
                {
                    $cached[] = $key;
                }
            }
        }

        if (null !== $cached)
        {
            $result = 'Кеширование выполнено для по следующим ключам:';

            foreach ($cached as $cache)
            {
                $result .= ' | ' . $cache;
            }

            $output->writeln([$result]);
        }

        $output->writeln('Кеширование не выполнено ни для одного профиля');

        return Command::SUCCESS;
    }
}
