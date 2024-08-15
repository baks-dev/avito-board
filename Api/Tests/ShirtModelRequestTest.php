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

namespace BaksDev\Avito\Board\Api\Tests;

use BaksDev\Avito\Board\Api\ShirtModelRequest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 */
#[When(env: 'test')]
final class ShirtModelRequestTest extends KernelTestCase
{
    public function brandHasModelsAndModelExist(): array
    {
        return [
            ["Nike Air Fear Of God 1 High"],
            ["Adidas By Stella McCartney"],
            ["Adidas 3-Stripes"],
            ["Adidas 4DFWD 2"],
            ["Adidas A.E. 1 Low"],
            ["Adidas Yeezy 500 Stone Salt"],
            ["Adidas Yeezy YEEZY 450 Resin"],
            ["1811 Eighteen One One"],
        ];
    }

    /**
     * @dataProvider brandHasModelsAndModelExist
     */
    public function testBrandHasModelsAndModelExist(string $model): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($model);
        self::assertNotNull($result);
    }

    /**
     * @dataProvider brandHasModelsAndModelExist
     */
    public function testBrandHasModelsAndModelExistLower(string $model): void
    {
        $model = mb_strtolower($model);

        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($model);
        self::assertNotNull($result);
    }

    /**
     * @dataProvider brandHasModelsAndModelExist
     */
    public function testBrandHasModelsAndModelExistRandom(string $model): void
    {
        $prepare = explode(' ', $model);
        natcasesort($prepare);
        $model = implode(' ', $prepare);

        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($model);
        self::assertNotNull($result);
    }

    public function brandHasModelsAndModelNotExist(): array
    {
        return [
            ["Nike", "Nike"],
            ["Nike Air", 'Nike'],
            ["Adidas", 'Adidas'],
            ["Adidas Original", 'Adidas'],
            ["Adidas Yeezy Stone", 'Adidas'],
        ];
    }

    /**
     * @dataProvider brandHasModelsAndModelNotExist
     */
    public function testBrandHasModelsAndModelNotExist(string $productName, string $brand): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($productName);
        self::assertNotNull($result);
        self::assertSame($brand, $result['brand']);
        self::assertSame('Другая', $result['model']);
    }

    /**
     * @dataProvider brandHasModelsAndModelNotExist
     */
    public function testBrandHasModelsAndModelNotExistLower(string $productName, string $brand): void
    {
        $lowModel = mb_strtolower($productName);

        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($lowModel);

        self::assertNotNull($result);
        self::assertSame($brand, $result['brand']);
        self::assertSame('Друга', $result['model']);
    }

    /**
     * @dataProvider brandHasModelsAndModelNotExist
     */
    public function testBrandHasModelsAndModelNotExistRandom(string $productName, string $brand): void
    {
        $prepare = explode(' ', $productName);
        natcasesort($prepare);
        $model = implode(' ', $prepare);

        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($model);

        self::assertNotNull($result);
        self::assertSame($brand, $result['brand']);
        self::assertSame('Другая', $result['model']);
    }

    public function brandHasNoModels(): array
    {
        return [
            ["& Other Stories"],
            ["12 STOREEZ"],
        ];
    }

    /**
     * @dataProvider brandHasNoModels
     */
    public function testRequestHasNotModel(string $model): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($model);

        self::assertNotNull($result);
        self::assertSame(null, $result['model']);
    }

    public function brandAndModelNotExist(): array
    {
        return [
            ["qweqwewq"],
            ["qweq-wewq"],
            ["qweq wewq"],
            ["1122"],
            ["11-22"],
            ["11 22"],
        ];
    }

    /**
     * @dataProvider brandAndModelNotExist
     */
    public function testBrandAndModelNotExist(string $model): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ShirtModelRequest $request */
        $request = $container->get(ShirtModelRequest::class);

        $result = $request->getModel($model);

        self::assertNull($result);
    }
}
