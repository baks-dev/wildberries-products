<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Products\Repository\Custom\AllProductsWithWildberriesImage\Tests;

use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Wildberries\Products\Forms\WildberriesCustomFilter\WildberriesProductsFilterDTO;
use BaksDev\Wildberries\Products\Repository\Custom\AllProductsWithWildberriesImage\AllProductsWithWildberriesImagesInterface;
use BaksDev\Wildberries\Products\Repository\Custom\AllProductsWithWildberriesImage\AllProductsWithWildberriesImagesResult;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('wildberries-products')]
final class AllProductsWithWildberriesImagesTest extends KernelTestCase
{
    public function testFindAll(): void
    {
        self::assertTrue(true);

        /** @var AllProductsWithWildberriesImagesInterface $allProductsWithWildberriesImages */
        $allProductsWithWildberriesImages = self::getContainer()
            ->get(AllProductsWithWildberriesImagesInterface::class);

        $WildberriesFilter = new WildberriesProductsFilterDTO();
        $result = $allProductsWithWildberriesImages
            ->filterWildberriesProducts($WildberriesFilter)
            ->findAll()
            ->getData();


        if(empty($result))
        {
            return;
        }


        /** @var AllProductsWithWildberriesImagesResult $AllProductsWithWildberriesImagesResult */
        foreach($result as $AllProductsWithWildberriesImagesResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(AllProductsWithWildberriesImagesResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($AllProductsWithWildberriesImagesResult);
                    // dump($data);
                }
            }
        }
    }
}