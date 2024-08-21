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

namespace BaksDev\Avito\Board\Repository\Form\__AllProductName;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;

final class AllProductNameRepository implements AllProductNameInterface
{
    private ?CategoryProductUid $category = null;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function category(CategoryProduct|CategoryProductUid|string $category): self
    {
        if($category instanceof CategoryProduct)
        {
            $category = $category->getId();
        }

        if(is_string($category))
        {
            $category = new CategoryProductUid();
        }

        $this->category = $category;
        return $this;
    }

    public function findAll(): ?CategoryProductSectionFieldUid
    {
        if(null === $this->category)
        {
            return null;
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->from(CategoryProduct::class, 'product_category');

        $dbal
            ->where('product_category.id = :category')
            ->setParameter(
                'category',
                $this->category,
                CategoryProductUid::TYPE
            );

        $dbal
            ->join(
                'product_category',
                ProductCategory::class,
                'product_categories_product',
                'product_categories_product.category = product_category.id AND product_categories_product.root = true'
            );

        $dbal->join(
            'product_category',
            Product::class,
            'product',
            'product_categories_product.event = product.event'
        );

        $dbal
            ->addSelect('product_trans.event as value')
            ->addSelect('product_trans.name as attr')
            ->join(
                'product_category',
                ProductTrans::class,
                'product_trans',
                'product.event = product_trans.event AND product_trans.local = :local'
            );

        return $dbal->fetchHydrate(CategoryProductSectionFieldUid::class);
    }
}
