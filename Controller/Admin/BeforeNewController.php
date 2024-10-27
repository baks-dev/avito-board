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

namespace BaksDev\Avito\Board\Controller\Admin;

use BaksDev\Avito\Board\UseCase\BeforeNew\AvitoBoardCategoryMapperDTO;
use BaksDev\Avito\Board\UseCase\BeforeNew\AvitoBoardCategoryMapperForm;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_BOARD_BEFORE_NEW')]
final class BeforeNewController extends AbstractController
{
    /**
     * Создание формы для маппинга локальной категории с категорией Авито для создания формы соотношения свойств
     */
    #[Route('/admin/avito-board/mapper/before_new', name: 'admin.mapper.beforenew', methods: ['POST', 'GET'])]
    public function beforeNew(Request $request): Response
    {
        $categoryMapperDTO = new AvitoBoardCategoryMapperDTO();

        $form = $this->createForm(AvitoBoardCategoryMapperForm::class, $categoryMapperDTO, [
            'action' => $this->generateUrl('avito-board:admin.mapper.beforenew'),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('mapper_before_new'))
        {
            $this->refreshTokenForm($form);

            return $this->redirectToRoute(
                'avito-board:admin.mapper.new',
                [
                    'localCategory' => $categoryMapperDTO->localCategory,
                    'avitoCategory' => $categoryMapperDTO->avitoCategory->getProductCategory(),
                ]
            );
        }

        return $this->render(['form' => $form->createView()]);
    }
}
