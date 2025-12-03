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

declare(strict_types=1);

namespace BaksDev\Wildberries\Products\Controller\Admin\Custom;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use BaksDev\Wildberries\Api\Wildberries;
use BaksDev\Wildberries\Products\Entity\Custom\WildberriesProductCustom;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\WildberriesCustomProductDTO;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\WildberriesCustomProductForm;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\WildberriesCustomProductHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_WB_PRODUCTS_CUSTOM_EDIT')]
class NewEditController extends AbstractController
{
    #[Route(
        '/admin/wb/custom/edit/{invariable}',
        name: 'admin.custom.edit',
        methods: ['GET', 'POST']
    )]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        WildberriesCustomProductHandler $WildberriesCustomProductHandler,
        ProductDetailByInvariableInterface $productDetailByInvariable,
        string|null $invariable = null,
    ): Response
    {
        $WildberriesCustomProductDTO = new WildberriesCustomProductDTO()
            ->setInvariable($invariable);

        /**
         * Находим уникальный продукт Wildberries, делаем его инстанс, передаем в форму
         *
         * @var WildberriesProductCustom|null $WildberriesProductCustom
         */
        $WildberriesProductCustom = $entityManager
            ->getRepository(WildberriesProductCustom::class)
            ->findOneBy(['invariable' => $invariable]);


        $WildberriesProductCustom?->getDto($WildberriesCustomProductDTO);

        $form = $this
            ->createForm(
                type: WildberriesCustomProductForm::class,
                data: $WildberriesCustomProductDTO,
                options: ['action' => $this->generateUrl(
                    'wildberries-products:admin.custom.edit', ['invariable' => $WildberriesCustomProductDTO->getInvariable(),],
                )],
            )
            ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('wildberries_product'))
        {
            $this->refreshTokenForm($form);

            $handle = $WildberriesCustomProductHandler->handle($WildberriesCustomProductDTO);

            $this->addFlash(
                'page.edit',
                $handle instanceof WildberriesProductCustom ? 'success.edit' : 'danger.edit',
                'wildberries-products.admin',
                $handle,
            );

            return $this->redirectToRoute('wildberries-products:admin.custom.index');
        }

        $ProductDetailByInvariableResult = $productDetailByInvariable
            ->invariable($invariable)
            ->find();

        return $this->render([
            'form' => $form->createView(),
            'product' => $ProductDetailByInvariableResult,
        ]);
    }
}