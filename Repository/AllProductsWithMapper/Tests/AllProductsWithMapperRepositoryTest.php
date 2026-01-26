<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Board\Repository\AllProductsWithMapper\Tests;

use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperResult;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
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
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('avito-board')]
class AllProductsWithMapperRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var AllProductsWithMapperInterface $AllProductsWithMapper */
        $AllProductsWithMapper = self::getContainer()->get(AllProductsWithMapperInterface::class);

        $AvitoTokenUid = $_SERVER['TEST_AVITO_PROFILE'] ?? AvitoTokenUid::TEST;

        $products = $AllProductsWithMapper
            //->forProfile(new UserProfileUid($profileUid))
            ->forAvitoToken(new AvitoTokenUid($AvitoTokenUid))
            ->findAll();

        if(false !== $products)
        {
            foreach($products as $AllProductsWithMapperResult)
            {
                // Вызываем все геттеры
                $reflectionClass = new ReflectionClass(AllProductsWithMapperResult::class);
                $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

                foreach($methods as $method)
                {
                    // Методы без аргументов
                    if($method->getNumberOfParameters() === 0)
                    {
                        // Вызываем метод
                        $data = $method->invoke($AllProductsWithMapperResult);
                        // dump($data);
                    }
                }
            }
        }

        self::assertTrue(true);
    }
}
