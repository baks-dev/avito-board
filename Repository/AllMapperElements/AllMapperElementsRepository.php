<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Board\Repository\AllMapperElements;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Cover\CategoryProductCover;
use BaksDev\Products\Category\Entity\Event\CategoryProductEvent;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;

final class AllMapperElementsRepository implements AllMapperElementsInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function findAll(): PaginatorInterface
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('avito_board.id')
            ->addSelect('avito_board.event')
            ->from(AvitoBoard::class, 'avito_board');

        $dbal->join(
            'avito_board',
            AvitoBoardEvent::class,
            'event',
            'event.id = avito_board.event AND event.category = avito_board.id',
        );

        /** Категория */
        $dbal
            ->addSelect('category.id as category_id')
            ->addSelect('category.event as category_event')
            ->join(
                'avito_board',
                CategoryProduct::class,
                'category',
                'category.id = avito_board.id',
            );

        /** События категории */
        $dbal->addSelect('category_event.sort');

        $dbal->join(
            'category',
            CategoryProductEvent::class,
            'category_event',
            'category_event.id = category.event',
        );

        /** Обложка */

        $dbal->leftJoin(
            'category_event',
            CategoryProductCover::class,
            'category_cover',
            'category_cover.event = category_event.id',
        );

        $dbal
            ->addSelect("
                CASE
                   WHEN category_cover.name IS NOT NULL THEN
                        CONCAT ( '/upload/".$dbal->table(CategoryProductCover::class)."' , '/', category_cover.name)
                   ELSE NULL
                END AS cover
			")
            ->addSelect('category_cover.ext')
            ->addSelect('category_cover.cdn');


        /** Перевод категории */
        $dbal
            ->addSelect('category_trans.name as category_name')
            ->addSelect('category_trans.description as category_description')
            ->leftJoin(
                'category_event',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category_event.id AND category_trans.local = :local',
            );

        return $this->paginator->fetchAllAssociative($dbal);
    }
}