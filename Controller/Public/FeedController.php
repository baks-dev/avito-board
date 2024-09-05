<?php

namespace BaksDev\Avito\Board\Controller\Public;

use BaksDev\Avito\Board\Repository\AllProductsWithMapper\AllProductsWithMapperInterface;
use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Cache\ItemInterface;

#[AsController]
final class FeedController extends AbstractController
{
    #[Route('/avito-board/{profile}/feed.xml', name: 'public.export.feed', methods: ['GET'])]
    public function feed(
        AppCacheInterface $cache,
        AllProductsWithMapperInterface $allProductsWithMapping,
        #[ParamConverter(UserProfileUid::class)] $profile,
    ): Response {

        $products = $allProductsWithMapping->findAll($profile);

        $cache = $cache->init('avito-board');

        $feed = $cache->get('feed-' . $profile, function (ItemInterface $item) use ($products): string {

            $item->expiresAfter(\DateInterval::createFromDateString('1 hour'));

            return $this->render(
                [
                    'products' => $products,
                ],
                file: 'export.html.twig'
            )->getContent();
        });

        $response = new Response($feed);
        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}
