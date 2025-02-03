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

namespace BaksDev\Wildberries\Products\Controller\Admin\Settings;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Wildberries\Products\Forms\Preform\PreformDTO;
use BaksDev\Wildberries\Products\Forms\Preform\PreformForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_WB_PRODUCTS_SETTING_NEW')]
final class PreFormController extends AbstractController
{

    #[Route('/admin/wb/product/setting/preforms', name: 'admin.settings.preform', methods: ['POST', 'GET'])]
    public function delete(
        Request $request,
    ): Response
    {
        $PreformDTO = new PreformDTO();

        $form = $this
            ->createForm(
                type: PreformForm::class,
                data: $PreformDTO,
                options: ['action' => $this->generateUrl('wildberries-products:admin.settings.preform'),]
            )
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('preform'))
        {
            $this->refreshTokenForm($form);

            return $this->redirectToRoute(
                'wildberries-products:admin.settings.newedit.new',
                ['id' => (string) $PreformDTO->getId(), 'parent' => $PreformDTO->getCategory()?->getId()]
            );
        }

        return $this->render(['form' => $form->createView(),]);
    }

}