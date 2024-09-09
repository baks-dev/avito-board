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

namespace BaksDev\Avito\Board\Controller\Admin;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\UseCase\Delete\AvitoBoardDeleteMapperDTO;
use BaksDev\Avito\Board\UseCase\Delete\AvitoBoardDeleteMapperForm;
use BaksDev\Avito\Board\UseCase\Delete\AvitoBoardDeleteMapperHandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_AVITO_BOARD_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/avito-board/mapper/delete/{id}', name: 'admin.mapper.delete', methods: ['POST', 'GET'])]
    public function delete(Request $request, #[MapEntity] AvitoBoardEvent $event, AvitoBoardDeleteMapperHandler $handler): Response
    {
        $deleteMapperDTO = new AvitoBoardDeleteMapperDTO();

        /** Гидрируем ДТО из события */
        $event->getDto($deleteMapperDTO);

        $form = $this->createForm(AvitoBoardDeleteMapperForm::class, $deleteMapperDTO, [
            'action' => $this->generateUrl(
                'avito-board:admin.mapper.delete',
                [
                    'id' => $deleteMapperDTO->getEvent()
                ]
            )
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('delete_mapper'))
        {
            $this->refreshTokenForm($form);

            $handlerResult = $handler->handle($deleteMapperDTO);

            if ($handlerResult instanceof AvitoBoard)
            {
                $this->addFlash('page.delete', 'success.delete', 'avito-board.admin');

                return $this->redirectToRoute('avito-board:admin.mapper.index');
            }

            $this->addFlash('page.delete', 'danger.delete', 'avito-board.admin', $handlerResult);

            return $this->redirectToRoute('avito-board:admin.mapper.index', status: 400);
        }

        return $this->render([
            'form' => $form->createView(),
        ]);
    }
}
