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

namespace BaksDev\Avito\Board\UseCase\Mapper\Tests;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Mapper\AvitoBoardMapper;
use BaksDev\Avito\Board\Entity\Modify\AvitoBoardModify;
use BaksDev\Avito\Board\Type\Mapper\Products\PassengerTire\PassengerTireProduct;
use BaksDev\Avito\Board\UseCase\Mapper\Delete\DeleteMapperDTO;
use BaksDev\Avito\Board\UseCase\Mapper\Delete\DeleteMapperHandler;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Modifier\AvitoTokenModify;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteDTO;
use BaksDev\Avito\UseCase\Admin\Delete\AvitoTokenDeleteHandler;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 */
#[When(env: 'test')]
final class AvitoBoardMapperDeleteTest extends KernelTestCase
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

    public function testDelete(): void
    {
        self::assertTrue(true);

        $event = self::$event;
        self::assertNotNull($event);

        $deleteMapperDTO = new DeleteMapperDTO();

        $event->getDto($deleteMapperDTO);

        $container = self::getContainer();

        /** @var DeleteMapperHandler $handler */
        $handler = $container->get(DeleteMapperHandler::class);
        $deleteAvitoBoard = $handler->handle($deleteMapperDTO);
        self::assertTrue($deleteAvitoBoard instanceof AvitoBoard);
    }

    public static function tearDownAfterClass(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

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
        $em->clear();
    }
}
