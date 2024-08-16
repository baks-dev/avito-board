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

use BaksDev\Avito\Board\Api\TireModelRequest;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 * @group avito-board-model-tire
 */
#[When(env: 'test')]
final class TireModelRequestTest extends KernelTestCase
{
    public function modelProvider(): array
    {
        return [
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
        ];
    }

    /**
     * @dataProvider modelProvider
     */
    public function testRequest(string $productName): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var TireModelRequest $request */
        $request = $container->get(TireModelRequest::class);

        $result = $request->getModel($productName);
        self::assertNotNull($result);

        $random = $this->random($productName);
        $result = $request->getModel($random);
        self::assertNotNull($result);
    }

    private function random(string $productName): string
    {
        $part = explode(' ', $productName);
        natcasesort($part);
        return implode(' ', $part);
    }
}
