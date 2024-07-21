<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Avito\Board\Controller\Admin\Categories;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\UseCase\Categories\BeforeNew\CategoryMapperDTO;
use BaksDev\Avito\Board\UseCase\Categories\BeforeNew\CategoryMapperForm;
use BaksDev\Avito\Board\UseCase\Categories\NewEdit\MapperSettingDTO;
use BaksDev\Avito\Board\UseCase\Categories\NewEdit\MapperSettingForm;
use BaksDev\Avito\Board\UseCase\Categories\NewEdit\MapperSettingsHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Category\Entity\CategoryProduct;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_PRODUCT_CATEGORIES_NEW')]
final class NewController extends AbstractController
{
    /**
     * Маппим нашу категорию с категорией Авито перед созданием формы сопоставления
     */
    #[Route('/admin/avito-board/categories/before_new', name: 'admin.categories.beforenew', methods: ['POST', 'GET'])]
    public function beforeNew(Request $request): Response
    {
        $mappingCategory = new CategoryMapperDTO();

        $form = $this->createForm(CategoryMapperForm::class, $mappingCategory, [
            'action' => $this->generateUrl('avito-board:admin.categories.beforenew'),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('avito_board_before_new'))
        {
            $this->refreshTokenForm($form);

            return $this->redirectToRoute(
                'avito-board:admin.categories.new',
                [
                    'category' => $mappingCategory->localCategory,
                    'avitoCategory' => $mappingCategory->avitoCategory->getRootCategory(),
                ]
            );
        }

        return $this->render(['form' => $form->createView(),]);
    }

    /**
     * Создание формы сопоставления
     */
    #[Route(
        '/admin/avito-board/categories/new/{category}/{avitoCategory}',
        name: 'admin.categories.new',
        requirements: ['category' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST',]
    )]
    public function new(
        Request                      $request,
        MapperSettingsHandler        $handler,
        #[MapEntity] CategoryProduct $category,
        string                       $avitoCategory
    ): Response {
        $newDTO = new MapperSettingDTO();
        $newDTO->setLocalCategory($category);
        $newDTO->setAvitoCategory($avitoCategory);

        $form = $this->createForm(MapperSettingForm::class, $newDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('categories_mapping'))
        {
            $this->refreshTokenForm($form);
            $handle = $handler->handle($newDTO);

            if($handle instanceof AvitoBoard)
            {
                $this->addFlash('page.new', 'success.new', 'avito-board.admin');

                return $this->redirectToRoute('avito-board:admin.categories.index');
            }

            $this->addFlash('page.new', 'danger.new', 'avito-board.admin');

            return $this->redirectToReferer();
        }

        return $this->render([
            'form' => $form->createView()
        ]);
    }
}
