<?php

namespace BaksDev\Avito\Board\UseCase\Mapper\Tests;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Elements\IdElement;
use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProduct;
use BaksDev\Avito\Board\Type\Mapper\Products\SweatersAndShirts\SweatersAndShirtsProduct;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperHandler;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditDTO;
use BaksDev\Avito\UseCase\Admin\NewEdit\AvitoTokenNewEditHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 */

#[When(env: 'test')]
class AvitoBoardMapperEditTest extends KernelTestCase
{
    private static CategoryProductUid $category;

    private static CategoryProductSectionFieldUid $field;

    private static PassengerTireProduct $product;

    private static ?AvitoBoardEvent $event = null;

    public static function setUpBeforeClass(): void
    {
        $container = self::getContainer();

        /** @var PassengerTireProduct $product */
        $product = $container->get(PassengerTireProduct::class);

        self::$product = $product;
        self::$category = new CategoryProductUid(CategoryProductUid::TEST);
        self::$field = new CategoryProductSectionFieldUid(CategoryProductSectionFieldUid::TEST);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        self::$event = $em->getRepository(AvitoBoardEvent::class)
            ->findOneBy(['category' => self::$category]);

        $em->clear();
    }

    public function testEdit(): void
    {
        $event = self::$event;
        self::assertNotNull($event);

        $editDTO = new MapperDTO();

        $event->getDto($editDTO);

        $mapperSettings = $editDTO->getMapperSetting();

        self::assertNull($mapperSettings->current()->getElementInstance());
        self::assertEquals(self::$product->getElement('Id')->element(), $mapperSettings->current()->getElement());

        $mapperSettings->clear();

        $mapperElementDTO = new MapperElementDTO();

        $addressElement = self::$product->getElement('Address');
        $mapperElementDTO->setElement($addressElement->element());
        self::assertSame($addressElement->element(), $mapperElementDTO->getElement());

        $mapperElementDTO->setElementInstance($addressElement);
        self::assertInstanceOf(AvitoBoardElementInterface::class, $mapperElementDTO->getElementInstance());

        $mapperElementDTO->setProductField(self::$field);
        self::assertSame(self::$field, $mapperElementDTO->getProductField());

        $editDTO->addMapperSetting($mapperElementDTO);

        $container = self::getContainer();

        /** @var MapperHandler $handler */
        $handler = $container->get(MapperHandler::class);
        $editAvitoBoard = $handler->handle($editDTO);
        self::assertTrue($editAvitoBoard instanceof AvitoBoard);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoTokenModify::class)
            ->find($editAvitoBoard->getEvent());

        self::assertTrue($modifier->equals(ModifyActionUpdate::ACTION));
    }
}
