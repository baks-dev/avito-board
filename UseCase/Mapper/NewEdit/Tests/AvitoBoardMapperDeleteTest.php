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

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Tests;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\UseCase\Mapper\Delete\DeleteMapperDTO;
use BaksDev\Avito\Board\UseCase\Mapper\Delete\DeleteMapperHandler;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\MapperDTO;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board-usecase
 *
 * @depends BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Tests\AvitoBoardMapperEditTest::class
 */
#[When(env: 'test')]
final class AvitoBoardMapperDeleteTest extends KernelTestCase
{
    public function testDelete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        // активное событие
        /** @var AvitoBoardEvent $event */
        $event = $em->createQueryBuilder()
            ->select('avito_board_event')
            ->from(AvitoBoardEvent::class, 'avito_board_event')
            ->join(AvitoBoard::class, 'avito_board', 'WITH', 'avito_board_event.id = avito_board.event')
            ->where('avito_board_event.category = :category')
            ->setParameter('category', CategoryProductUid::TEST, CategoryProductUid::TYPE)
            ->getQuery()
            ->getOneOrNullResult();

        $em->clear();

        if ($event)
        {
            $editDTO = new MapperDTO();

            $event->getDto($editDTO);

            /** @var ArrayCollection<int, MapperElementDTO> $mapperElements */
            $mapperElements = $editDTO->getMapperSetting();
            $id = $mapperElements->first();
            $address = $mapperElements->last();

            self::assertEquals('IdEdit', $id->getElement());
            self::assertTrue($id->getProductField()->equals(CategoryProductSectionFieldUid::TEST));
            self::assertEquals('DefEdit', $id->getDef());

            self::assertEquals('AddressEdit', $address->getElement());
            self::assertTrue($address->getProductField()->equals(CategoryProductSectionFieldUid::TEST));
            self::assertEquals('DefEdit', $address->getDef());

            $deleteMapperDTO = new DeleteMapperDTO();

            $event->getDto($deleteMapperDTO);

            $container = self::getContainer();

            /** @var DeleteMapperHandler $handler */
            $handler = $container->get(DeleteMapperHandler::class);
            $deleteAvitoBoard = $handler->handle($deleteMapperDTO);
            self::assertTrue($deleteAvitoBoard instanceof AvitoBoard);
        }

        self::assertTrue(true);
    }

    public static function tearDownAfterClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $events = $em->getRepository(AvitoBoardEvent::class)
            ->findBy(['category' => CategoryProductUid::TEST]);

        foreach ($events as $event)
        {
            $em->remove($event);
        }

        $em->flush();
        $em->clear();
    }
}
