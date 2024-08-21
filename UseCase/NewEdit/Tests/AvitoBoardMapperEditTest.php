<?php

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Tests;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardModify;
use BaksDev\Avito\Board\Type\Doctrine\Event\AvitoBoardEventUid;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 * @group avito-board-usecase
 *
 * @depends BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Tests\AvitoBoardMapperNewTest::class
 *
 */
#[When(env: 'test')]
class AvitoBoardMapperEditTest extends KernelTestCase
{
    public function testEdit(): void
    {
        $container = self::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $event = $em
            ->getRepository(AvitoBoardEvent::class)
            ->find(AvitoBoardEventUid::TEST);

        self::assertNotNull($event);

        $editDTO = new MapperDTO();

        $event->getDto($editDTO);

        self::assertTrue($editDTO->getCategory()->equals(CategoryProductUid::TEST));

        /** @var MapperElementDTO $mapperElement */
        $mapperElement = $editDTO->getMapperSetting()->current();
        self::assertEquals('IdNew', $mapperElement->getElement());
        self::assertEquals('DefNew', $mapperElement->getDef());

        $mapperElement->setElement('IdEdit');
        $mapperElement->setDef('DefEdit');

        // добавляем новый маппер в коллекцию
        $mapperElementDTO = new MapperElementDTO();
        $mapperElementDTO->setElement('AddressEdit');
        $mapperElementDTO->setDef('DefEdit');
        $mapperElementDTO->setProductField(new CategoryProductSectionFieldUid());

        $editDTO->addMapperSetting($mapperElementDTO);

        $container = self::getContainer();

        /** @var MapperHandler $handler */
        $handler = $container->get(MapperHandler::class);
        $editAvitoBoard = $handler->handle($editDTO);
        self::assertTrue($editAvitoBoard instanceof AvitoBoard);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoBoardModify::class)
            ->find($editAvitoBoard->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
