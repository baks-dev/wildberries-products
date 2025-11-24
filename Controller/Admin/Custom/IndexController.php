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
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Wildberries\Products\Forms\WildberriesCustomFilter\WildberriesProductsFilterDTO;
use BaksDev\Wildberries\Products\Forms\WildberriesCustomFilter\WildberriesProductsFilterForm;
use BaksDev\Wildberries\Products\Repository\Custom\AllProductsWithWildberriesImage\AllProductsWithWildberriesImagesInterface;
use BaksDev\Wildberries\Products\Repository\Custom\AllProductsWithWildberriesImage\AllProductsWithWildberriesImagesResult;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_WB_PRODUCTS_CUSTOM_INDEX')]
final class IndexController extends AbstractController
{
    /**
     * @see IndexController
     * return 'wildberries-products:'.IndexController::NAME;
     */
    public const string NAME = 'admin.custom.index';

    #[Route('/admin/wb/custom/{page<\d+>}', name: self::NAME, methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        AllProductsWithWildberriesImagesInterface $AllProductsWithWildberriesImagesRepository,
        int $page = 0,
    ): Response
    {
        // Поиск
        $search = new SearchDTO();
        $searchForm = $this
            ->createForm(
                type: SearchForm::class,
                data: $search,
                options: ['action' => $this->generateUrl('wildberries-products:admin.custom.index')],
            )
            ->handleRequest($request);

        /**
         * Фильтр продукции по ТП
         */
        $productFilterDTO = new ProductFilterDTO();
        $ProductFilterForm = $this
            ->createForm(
                type: ProductFilterForm::class,
                data: $productFilterDTO,
                options: ['action' => $this->generateUrl('wildberries-products:admin.custom.index'),],
            )
            ->handleRequest($request);


        $WildberriesProductsFilterDTO = new WildberriesProductsFilterDTO();
        $WildberriesProductsFilterForm = $this
            ->createForm(
                type: WildberriesProductsFilterForm::class,
                data: $WildberriesProductsFilterDTO,
                options: ['action' => $this->generateUrl('wildberries-products:admin.custom.index'),],
            )
            ->handleRequest($request);


        /** @var AllProductsWithWildberriesImagesResult $products */
        $products = $AllProductsWithWildberriesImagesRepository
            ->search($search)
            ->filter($productFilterDTO)
            ->filterWildberriesProducts($WildberriesProductsFilterDTO)
            ->findAll();


        return $this->render([
            'filter' => $ProductFilterForm->createView(),
            'WildberriesProductsFilterForm' => $WildberriesProductsFilterForm->createView(),
            'search' => $searchForm->createView(),
            'query' => $products,
        ]);
    }
}