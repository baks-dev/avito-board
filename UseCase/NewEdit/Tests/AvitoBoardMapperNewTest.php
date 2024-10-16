<?php

namespace BaksDev\Avito\Board\UseCase\NewEdit\Tests;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardModify;
use BaksDev\Avito\Board\UseCase\NewEdit\AvitoBoardMapperDTO;
use BaksDev\Avito\Board\UseCase\NewEdit\AvitoBoardMapperHandler;
use BaksDev\Avito\Board\UseCase\NewEdit\Elements\AvitoBoardMapperElementDTO;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 * @group avito-board-usecase
 *
 * @depends BaksDev\Avito\Board\Controller\Admin\Tests\BeforeNewControllerTest::class
 */
#[When(env: 'test')]
class AvitoBoardMapperNewTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $avitoBoard = $em->getRepository(AvitoBoard::class)
            ->find(CategoryProductUid::TEST);

        if($avitoBoard)
        {
            $em->remove($avitoBoard);
        }

        $avitoBoardEvent = $em->getRepository(AvitoBoardEvent::class)
            ->findBy(['category' => CategoryProductUid::TEST]);

        foreach($avitoBoardEvent as $event)
        {
            $em->remove($event);
        }

        $em->flush();
        $em->clear();
    }

    public function testNew(): void
    {
        $newDTO = new AvitoBoardMapperDTO();

        // добавляем категории
        $newDTO->setCategory(new CategoryProductUid(CategoryProductUid::TEST));
        self::assertTrue($newDTO->getCategory()->equals(CategoryProductUid::TEST));

        $newDTO->setAvito('Легковые шины');
        self::assertSame('Легковые шины', $newDTO->getAvito());

        // добавляем элементы для маппинга
        $mapperElementDTO = new AvitoBoardMapperElementDTO();

        $mapperElementDTO->setElement('Id');
        self::assertSame('Id', $mapperElementDTO->getElement());

        $mapperElementDTO->setProductField(new CategoryProductSectionFieldUid());
        self::assertTrue($mapperElementDTO->getProductField()->equals(CategoryProductSectionFieldUid::TEST));

        $mapperElementDTO->setDef('DefNew');
        self::assertSame('DefNew', $mapperElementDTO->getDef());

        $newDTO->addMapperElement($mapperElementDTO);

        $container = self::getContainer();

        /** @var AvitoBoardMapperHandler $handler */
        $handler = $container->get(AvitoBoardMapperHandler::class);
        $newAvitoBoard = $handler->handle($newDTO);
        self::assertTrue($newAvitoBoard instanceof AvitoBoard);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $modifier = $em->getRepository(AvitoBoardModify::class)
            ->find($newAvitoBoard->getEvent());

        self::assertTrue($modifier->equals(ModifyActionNew::ACTION));
    }
}
