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

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\UseCase\NewEdit\AvitoBoardMapperDTO;
use BaksDev\Avito\Board\UseCase\NewEdit\AvitoBoardMapperForm;
use BaksDev\Avito\Board\UseCase\NewEdit\AvitoBoardMapperHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Category\Entity\CategoryProduct;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_BOARD_NEW')]
final class NewController extends AbstractController
{
    /**
     * Создание формы сопоставления элементов категорий
     */
    #[Route(
        '/admin/avito-board/mapper/new/{localCategory}/{avitoCategory}',
        name: 'admin.mapper.new',
        requirements: [
            'localCategory' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$',
        ],
        methods: ['GET', 'POST']
    )]
    public function new(
        Request $request,
        AvitoBoardMapperHandler $handler,
        #[MapEntity] CategoryProduct $localCategory,
        string $avitoCategory
    ): Response
    {

        $mapperDTO = new AvitoBoardMapperDTO();
        $mapperDTO->setCategory($localCategory);
        $mapperDTO->setAvito($avitoCategory);

        $form = $this->createForm(AvitoBoardMapperForm::class, $mapperDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('mapper_new'))
        {
            $this->refreshTokenForm($form);

            $result = $handler->handle($mapperDTO);

            if($result instanceof AvitoBoard)
            {
                $this->addFlash('page.new', 'success.new', 'avito-board.admin');

                return $this->redirectToRoute('avito-board:admin.mapper.index');
            }

            $this->addFlash('page.new', 'danger.new', 'avito-board.admin', $result);

            return $this->redirectToReferer();
        }

        return $this->render(['form' => $form->createView()]);
    }
}
