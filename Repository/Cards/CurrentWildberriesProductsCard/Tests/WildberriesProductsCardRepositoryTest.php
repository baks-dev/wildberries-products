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

namespace BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\Tests;

use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries-products
 */
#[When(env: 'test')]
class WildberriesProductsCardRepositoryTest extends KernelTestCase
{
    public function testResult(): void
    {
        /** @var WildberriesProductsCardInterface $WildberriesProductsCard */
        $WildberriesProductsCard = self::getContainer()->get(WildberriesProductsCardInterface::class);

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        foreach($AllProductsIdentifier->findAll() as $key => $ProductsIdentifierResult)
        {
            if($key >= 10)
            {
                break;
            }

            $new = $WildberriesProductsCard
                ->forProfile(UserProfileUid::TEST)
                ->forProduct($ProductsIdentifierResult->getProductId())
                ->forOfferConst($ProductsIdentifierResult->getProductOfferConst())
                ->forVariationConst($ProductsIdentifierResult->getProductVariationConst())
                ->forModificationConst($ProductsIdentifierResult->getProductModificationConst())
                ->findResult();

            if(true === empty($new))
            {
                continue;
            }

            /** @var WildberriesProductsCardResult $new */
            self::assertInstanceOf(ProductUid::class, $new->getProductUid());
            self::assertInstanceOf(ProductOfferConst::class, $new->getOfferConst());
            self::assertIsString($new->getProductImages());
            self::assertIsInt($new->getProductOldPrice());

            self::assertTrue($new->getProductOfferValue() === null || true === is_string($new->getProductOfferValue()));
            self::assertTrue($new->getProductOfferPostfix() === null || true === is_string($new->getProductOfferPostfix()));
            self::assertTrue($new->getProductCard() === null || true === is_string($new->getProductCard()));
            self::assertTrue($new->getArticle() === null || true === is_string($new->getArticle()));
            self::assertTrue($new->getSearchArticle() === null || true === is_string($new->getSearchArticle()));
            self::assertTrue($new->getVariationConst() === null || $new->getVariationConst() instanceof ProductVariationConst);
            self::assertTrue($new->getProductVariationValue() === null || true === is_string($new->getProductVariationValue()));
            self::assertTrue($new->getProductVariationPostfix() === null || true === is_string($new->getProductVariationPostfix()));
            self::assertTrue($new->getProductSize() === null || true === is_string($new->getProductSize()));
            self::assertTrue($new->getProductName() === null || true === is_string($new->getProductName()));
            self::assertTrue($new->getProductPreview() === null || true === is_string($new->getProductPreview()));
            self::assertTrue($new->getCategoryName() === null || true === is_string($new->getCategoryName()));
            self::assertTrue($new->getLength() === null || true === is_int($new->getLength()));
            self::assertTrue($new->getWidth() === null || true === is_int($new->getWidth()));
            self::assertTrue($new->getHeight() === null || true === is_int($new->getHeight()));
            self::assertTrue($new->getWeight() === null || true === is_int($new->getWeight()));
            self::assertTrue($new->getProductProperty() === null || true === is_string($new->getProductProperty()));
            self::assertTrue($new->getProductParams() === null || true === is_string($new->getProductParams()));
            self::assertTrue($new->getProductCurrency() === null || true === is_string($new->getProductCurrency()));
            self::assertTrue($new->getProductQuantity() === null || true === is_int($new->getProductQuantity()));
            self::assertTrue($new->getMarketCategory() === null || true === is_int($new->getMarketCategory()));

            break;
        }

        self::assertTrue(true);
    }
}
