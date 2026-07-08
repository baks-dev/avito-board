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

namespace BaksDev\Avito\Board\Api\Tests;

use BaksDev\Avito\Board\Api\TireBrandRequest;
use BaksDev\Avito\Board\Api\TireModelRequest;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('avito-board')]
final class TireModelRequestTest extends KernelTestCase
{
    public static function modelProvider(): array
    {
        return [
            ["Westlake H188"],
            ["Trazano Terra Legend SL399 Owl"],
            ["Kumho WinterCraft Ice WI32"],
            ["Triangle Sportex XL TH201"],
            ["Sport SA-37 Westlake"],
            ["Westlake MUD LEGEND SL366"],
            ["Sailun Atrezzo Elite"],
            ["Sailun Atrezzo ZSR"],
            ["Zmax Gallopro H-T"],
            ["Zmax Zealion"],
            ["iLINK L-Zeal56"],
            ["Sailun Atrezzo ECO"],
            ["Taganca МШЗ М-233"],
            ["Triangle TRY88"],
            ["Triangle seasonx van ta702"],
            ["Trazano SL315 Trac Legend"],
        ];
    }

    #[DataProvider(methodName: 'modelProvider')]
    public function testRequest(string $productName): void
    {
        self::assertTrue(true);
        //return;

        self::bootKernel();

        $container = static::getContainer();


        /** @var TireBrandRequest $TireBrandRequest */
        $TireBrandRequest = $container->get(TireBrandRequest::class);

        $brand = $TireBrandRequest
            ->brand($productName)
            ->find();

        if(empty($brand))
        {
            echo PHP_EOL.sprintf(
                    'avito-board: Бренд продукта %s не найдена', $productName,
                ).self::class.':'.__LINE__.PHP_EOL;

            return;
        }

        self::assertNotEmpty($brand);

        /** ----------------------------------------------------------- */

        /** @var TireModelRequest $TireModelRequest */
        $TireModelRequest = $container->get(TireModelRequest::class);

        $model = $TireModelRequest
            ->brand($brand)
            ->model($productName)
            ->find();

        if(empty($model))
        {
            echo PHP_EOL.sprintf(
                    'avito-board: Модель продукта %s не найдена', $productName,
                ).self::class.':'.__LINE__.PHP_EOL;

            return;
        }

        self::assertNotEmpty($model);
    }
}
