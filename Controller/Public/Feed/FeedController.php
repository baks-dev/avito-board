<?php

namespace BaksDev\Avito\Board\Controller\Public\Feed;

use BaksDev\Avito\Board\Repository\Feed\AllProducts\AllProductsWithMappingInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Type\UidType\ParamConverter;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
class FeedController extends AbstractController
{
    #[Route('/avito-board/{profile}/feed.xml', name: 'public.export.feed', methods: ['GET'])]
    public function feed(
        AllProductsWithMappingInterface $allProductsWithMapping,
        #[ParamConverter(UserProfileUid::class)] $profile,
    ): Response {

        $products = $allProductsWithMapping->findAll();

        $response = $this->render(
            [
                'products' => $products,
            ],
            file: 'export.html.twig'
        );

        $response->headers->set('Content-Type', 'application/xml');

        return $response;
    }
}
