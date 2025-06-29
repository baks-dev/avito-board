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

namespace BaksDev\Avito\Board\Controller\Public;

use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use DateInterval;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;

#[AsController]
final class FeedController extends AbstractController
{
    #[Route('/avito-board/{profile}/feed.xml', name: 'public.export.feed', methods: ['GET'])]
    public function feed(
        AppCacheInterface $appCache,
        AllProductsWithMapperInterface $allProductsWithMapping,
        #[ParamConverter(UserProfileUid::class)] UserProfileUid $profile,
    ): Response
    {

        $cache = $appCache->init('avito-board');
        //$cache->deleteItem('feed-'.$profile); // отключаем кеш для debug

        $feed = $cache->get('feed-'.$profile, function(ItemInterface $item) use (
            $allProductsWithMapping,
            $profile
        ): string {

            $item->expiresAfter(DateInterval::createFromDateString('1 hour'));

            $products = $allProductsWithMapping
                ->forProfile($profile)
                ->findAll();

            return $this->render(['products' => $products], file: 'export.html.twig')->getContent();
        });

        $response = new Response($feed);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}
