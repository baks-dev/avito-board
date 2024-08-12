<?php

namespace BaksDev\Avito\Board\UseCase\Mapper\Tests;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Mapper\AvitoBoardMapper;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardModify;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Elements\IdElement;
use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProduct;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperHandler;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 */
#[When(env: 'test')]
class AvitoBoardMapperNewTest extends KernelTestCase
{
    private static CategoryProductUid $category;
    private static CategoryProductSectionFieldUid $field;

    private static PassengerTireProduct $product;

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

        $avitoBoard = $em->getRepository(AvitoBoard::class)
            ->find(self::$category);

        if ($avitoBoard)
        {
            $em->remove($avitoBoard);

            $avitoBoardEvent = $em->getRepository(AvitoBoardEvent::class)
                ->findBy(['category' => self::$category]);

            foreach ($avitoBoardEvent as $event)
            {
                // нахожу мапперы
                $mappers = $em->getRepository(AvitoBoardMapper::class)
                    ->findBy(['event' => $event->getId()]);

                foreach ($mappers as $mapper)
                {
                    $em->remove($mapper);
                }

                // нахожу модификатор
                $modifier = $em->getRepository(AvitoBoardModify::class)
                    ->findOneBy(['event' => $event->getId()]);

                $em->remove($modifier);
                $em->remove($event);
            }

            $em->flush();
        }

        $em->clear();
    }

    public function testNew(): void
    {
        $newDTO = new MapperDTO();

        $newDTO->setCategory(self::$category);
        self::assertSame(self::$category, $newDTO->getCategory());

        $newDTO->setAvito(self::$product->getProductCategory());
        self::assertSame(self::$product->getProductCategory(), $newDTO->getAvito());

        $mapperElementDTO = new MapperElementDTO();

        $idElement = self::$product->getElement('Id');
        self::assertEquals(IdElement::class, $idElement::class);

        $mapperElementDTO->setElement($idElement->element());
        self::assertSame($idElement->element(), $mapperElementDTO->getElement());

        $mapperElementDTO->setElementInstance($idElement);
        self::assertInstanceOf(AvitoBoardElementInterface::class, $mapperElementDTO->getElementInstance());

        $mapperElementDTO->setProductField(self::$field);
        self::assertSame(self::$field, $mapperElementDTO->getProductField());

        $newDTO->addMapperSetting($mapperElementDTO);

        $container = self::getContainer();

        /** @var MapperHandler $handler */
        $handler = $container->get(MapperHandler::class);
        $newAvitoBoard = $handler->handle($newDTO);
        self::assertTrue($newAvitoBoard instanceof AvitoBoard);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoBoardModify::class)
            ->find($newAvitoBoard->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));
    }
}
