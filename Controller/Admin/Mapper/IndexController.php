<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Controller\Admin\Mapper;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Yandex\Market\Products\Repository\Settings\AllProductsSettings\AllProductsSettingsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use BaksDev\Core\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
#[RoleSecurity('ROLE_AVITO_PRODUCT_MAPPER_INDEX')]
final class IndexController extends AbstractController
{
    /**
     * Настройки соотношения категорий с Avito
     */
    #[Route('/admin/avito-board/mapper/categories/{page<\d+>}', name: 'admin.mapper.index', methods: ['GET', 'POST'])]
    public function index(Request $request, AllProductsSettingsInterface $allProductsSettings, int $page = 0): Response
    {

        /* Поиск */
        $search = new SearchDTO();
        $searchForm = $this->createForm(
            SearchForm::class,
            $search,
            [
                'action' => $this->generateUrl('avito-board:admin.mapper.index')
            ]
        );

        $searchForm->handleRequest($request);

        /* Получаем список */
        $query = $allProductsSettings->fetchAllProductsSettingsAssociative();

        return $this->render(
            [
                'query' => $query,
                'search' => $searchForm->createView(),
            ],
        );
    }
}
