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

namespace BaksDev\Avito\Board\Api\Tests;

use BaksDev\Avito\Board\Api\ShirtModelRequest;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('avito-board')]
final class ShirtModelRequestTest extends KernelTestCase
{
    public static array $productNames;
    public static ShirtModelRequest $request;

    public static function setUpBeforeClass(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        self::$request = $container->get(ShirtModelRequest::class);

        self::$productNames = [
            'brandHasModelsAndModelExist' =>
                [
                    "Nike Air Fear Of God 1 High",
                    "Adidas By Stella McCartney",
                    "Adidas 3-Stripes",
                    "Adidas 4DFWD 2",
                    "Adidas 3-Stripe",
                    "Adidas A.E. 1 Low",
                    "Adidas Yeezy 500 Stone Salt",
                    "Adidas Yeezy YEEZY 450 Resin",
                    "1811 Eighteen One One",
                ],
            'brandHasModelsAndModelNotExist' =>
                [
                    [
                        'productName' => "Nike",
                        'brand' => "Nike"
                    ],
                    [
                        'productName' => "Nike Air",
                        'brand' => "Nike"
                    ],
                    [
                        'productName' => "Adidas",
                        'brand' => "Adidas"
                    ],
                    [
                        'productName' => "Adidas Original",
                        'brand' => "Adidas"
                    ],
                    [
                        'productName' => "Adidas Yeezy Stone",
                        'brand' => "Adidas"
                    ],
                ],
            'brandHasNoModels' =>
                [
                    "& Other Stories",
                    "12 STOREEZ",
                ],
            'brandAndModelNotExist' =>
                [
                    "qweqwewq",
                    "qweq-wewq",
                    "qweq wewq",
                    "1122",
                    "11-22",
                    "11 22",
                ],
        ];
    }

    public function testBrandHasModelsAndModelExist(): void
    {
        $productNames = self::$productNames['brandHasModelsAndModelExist'];

        foreach($productNames as $productName)
        {
            $result = self::$request->getModel($productName);
            self::assertNotNull($result);

            $random = $this->random($productName);

            $result = self::$request->getModel($random);
            self::assertNotNull($result);
        }
    }

    public function testBrandHasModelsAndModelNotExist(): void
    {
        $productNames = self::$productNames['brandHasModelsAndModelNotExist'];

        foreach($productNames as $key => $productName)
        {
            $result = self::$request->getModel($productName['productName']);
            self::assertNotNull($result);
            self::assertSame($productName['brand'], $result['brand']);
            self::assertSame('Другая', $result['model']);

            $random = $this->random($productName['productName']);

            $result = self::$request->getModel($random);
            self::assertNotNull($result);
            self::assertSame($productName['brand'], $result['brand']);
            self::assertSame('Другая', $result['model']);
        }
    }

    public function testBrandHasNoModels(): void
    {
        $productNames = self::$productNames['brandHasNoModels'];

        foreach($productNames as $key => $productName)
        {
            $result = self::$request->getModel($productName);
            self::assertNotNull($result);
            self::assertSame(null, $result['model']);

            $random = $this->random($productName);

            $result = self::$request->getModel($random);
            self::assertNotNull($result);
            self::assertSame(null, $result['model']);


        }
    }

    public function testBrandAndModelNotExist(): void
    {
        $productNames = self::$productNames['brandAndModelNotExist'];

        foreach($productNames as $productName)
        {
            $result = self::$request->getModel($productName);
            self::assertNull($result);

            $random = $this->random($productName);

            $result = self::$request->getModel($random);
            self::assertNull($result);
        }
    }

    private function random(string $productName): string
    {
        $part = explode(' ', $productName);
        natcasesort($part);
        return implode(' ', $part);
    }
}
