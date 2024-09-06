<?php

namespace BaksDev\Avito\Board\Schedule\RefreshFeed\Tests;

use BaksDev\Core\Messenger\MessageDispatch;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 * @group avito-board-schedule
 */
#[When(env: 'test')]
class RefreshFeedScheduleHandlerTest extends KernelTestCase
{

    public function testHandler(): void
    {
        $container = self::getContainer();

        /** @var MessageDispatch $dispatch */
        $dispatch = $container->get(MessageDispatch::class);


        dump($dispatch->isConsumer('avito-board'));
        dd();
    }
}
