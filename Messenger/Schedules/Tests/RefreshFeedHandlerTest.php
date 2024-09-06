<?php

namespace BaksDev\Avito\Board\Messenger\Schedules\Tests;

use BaksDev\Avito\Board\Messenger\Schedules\RefreshFeedHandler;
use BaksDev\Avito\Board\Messenger\Schedules\RefreshFeedMessage;
use BaksDev\Core\Messenger\MessageDispatch;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 * @group avito-board-handler
 */
#[When(env: 'test')]
class RefreshFeedHandlerTest extends KernelTestCase
{

    public static function setUpBeforeClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

    }

    public function testHandler(): void
    {
        $container = self::getContainer();

        /** @var MessageDispatch $dispatch */
        $dispatch = $container->get(MessageDispatch::class);

        $message = new RefreshFeedMessage(new UserProfileUid(UserProfileUid::TEST));

        /** @var RefreshFeedHandler $handler */
        $handler = $container->get(RefreshFeedHandler::class);

        dd($handler($message));
    }
}
