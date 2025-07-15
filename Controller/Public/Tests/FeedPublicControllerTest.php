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

namespace BaksDev\Avito\Board\Controller\Public\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group avito-board
 * @group avito-board-controller-feed
 *
 */
#[When(env: 'test')]
final class FeedPublicControllerTest extends WebTestCase
{
    private const string URL = '/avito-board/%s/feed.xml';

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {
        $profileUid = $_SERVER['TEST_PROFILE'] ?? UserProfileUid::TEST;

        $url = sprintf(self::URL, $profileUid);

        self::ensureKernelShutdown();
        $client = static::createClient();

        $start = hrtime(true);
        $client->request('GET', $url);
        $end = hrtime(true);

        self::assertResponseStatusCodeSame(200);

        //        $durationMs = ($end - $start) / 1e+6;
        //        $durationSec = $durationMs / 1000;     // секунды
        //        dump("⏱ Время ответа: " . number_format($durationSec, 2) . " sec");
    }
}
