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
 *
 */

declare(strict_types=1);

namespace BaksDev\Avito\Board\Repository\AllProductsWithMapper\Tests;

use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Offers\Id\CategoryProductOffersUid;
use BaksDev\Products\Category\Type\Offers\Modification\CategoryProductModificationUid;
use BaksDev\Products\Category\Type\Offers\Variation\CategoryProductVariationUid;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 */
#[When(env: 'test')]
class AllProductsWithMapperRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var AllProductsWithMapperInterface $AllProductsWithMapper */
        $AllProductsWithMapper = self::getContainer()->get(AllProductsWithMapperInterface::class);

        $profileUid = $_SERVER['TEST_PROFILE'] ?? UserProfileUid::TEST;

        $products = $AllProductsWithMapper
            ->forProfile(new UserProfileUid($profileUid))
            ->findAll();

        if(false !== $products)
        {
            foreach($products as $product)
            {
                /* Testing Product Basic Info */
                self::assertInstanceOf(ProductUid::class, $product->getProductId());
                self::assertInstanceOf(ProductEventUid::class, $product->getProductEvent());
                self::assertIsString($product->getProductName());
                self::assertIsString($product->getProductArticle());
                self::assertIsBool($product->isCategoryActive());

                /* Testing Dates */
                self::assertIsString($product->getProductDateBegin());
                is_string($product->getProductDateOver()) ?: self::assertNull($product->getProductDateOver());

                /* Testing Product Description */
                is_string($product->getProductDescription()) ?: self::assertNull($product->getProductDescription());

                /* Testing Offer Related */
                is_null($product->getProductOfferId()) ?: self::assertInstanceOf(ProductOfferUid::class, $product->getProductOfferId());
                is_null($product->getProductOfferConst()) ?: self::assertInstanceOf(ProductOfferConst::class, $product->getProductOfferConst());
                is_string($product->getProductOfferValue()) ?: self::assertNull($product->getProductOfferValue());
                is_string($product->getProductOfferPostfix()) ?: self::assertNull($product->getProductOfferPostfix());
                is_null($product->getOfferSectionFieldUid()) ?: self::assertInstanceOf(CategoryProductOffersUid::class, $product->getOfferSectionFieldUid());
                self::assertIsString($product->getProductOfferReference());

                /* Testing Variation Related */
                is_null($product->getProductVariationId()) ?: self::assertInstanceOf(ProductVariationUid::class, $product->getProductVariationId());
                is_null($product->getProductVariationConst()) ?: self::assertInstanceOf(ProductVariationConst::class, $product->getProductVariationConst());
                is_string($product->getProductVariationValue()) ?: self::assertNull($product->getProductVariationValue());
                is_string($product->getProductVariationPostfix()) ?: self::assertNull($product->getProductVariationPostfix());
                is_null($product->getVariationSectionFieldUid()) ?: self::assertInstanceOf(CategoryProductVariationUid::class, $product->getVariationSectionFieldUid());

                /* Testing Modification Related */
                is_string($product->getProductModificationId()) ?: self::assertNull($product->getProductModificationId());
                is_null($product->getProductModificationConst()) ?: self::assertInstanceOf(ProductModificationUid::class, $product->getProductModificationConst());
                is_string($product->getProductModificationValue()) ?: self::assertNull($product->getProductModificationValue());
                is_string($product->getProductModificationPostfix()) ?: self::assertNull($product->getProductModificationPostfix());
                is_null($product->getModificationSectionFieldUid()) ?: self::assertInstanceOf(CategoryProductModificationUid::class, $product->getModificationSectionFieldUid());

                /* Testing Category and Price */
                self::assertIsString($product->getProductCategory()); // Изменено с assertInstanceOf на assertIsString
                is_bool($product->getProductPrice()) ? self::assertFalse($product->getProductPrice()) : self::assertInstanceOf(Money::class, $product->getProductPrice());
                self::assertInstanceOf(Currency::class, $product->getProductCurrency());
                self::assertIsInt($product->getProductQuantity());

                /* Testing Images */
                is_array($product->getProductImages()) ?: self::assertNull($product->getProductImages());
                is_array($product->getAvitoProductImages()) ?: self::assertNull($product->getAvitoProductImages());

                /* Testing Delivery Info */
                is_int($product->getProductLengthDelivery()) ?: self::assertNull($product->getProductLengthDelivery());
                is_int($product->getProductWidthDelivery()) ?: self::assertNull($product->getProductWidthDelivery());
                is_int($product->getProductHeightDelivery()) ?: self::assertNull($product->getProductHeightDelivery());
                is_int($product->getProductWeightDelivery()) ?: self::assertNull($product->getProductWeightDelivery());

                /* Testing Avito Specific */
                self::assertIsInt($product->getAvitoKitValue());
                self::assertIsString($product->getAvitoProfilePercent());
                self::assertIsString($product->getAvitoProfileAddress());
                self::assertIsString($product->getAvitoProfileManager());
                self::assertIsString($product->getAvitoProfilePhone());
                is_array($product->getAvitoBoardMapper()) ?: self::assertNull($product->getAvitoBoardMapper());
                self::assertInstanceOf(CategoryProductUid::class, $product->getAvitoBoardMapperCategoryId());
                self::assertIsString($product->getAvitoBoardAvitoCategory());
                is_string($product->getAvitoProductDescription()) ?: self::assertNull($product->getAvitoProductDescription());
            }
        }

        self::assertTrue(true);
    }
}
