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

declare(strict_types=1);

namespace BaksDev\Wildberries\Products\Repository\Barcode\WbBarcodeSettings;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;

final class WbBarcodeSettingsRepository implements WbBarcodeSettingsInterface
{

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}


    public function findWbBarcodeSettings(Product|ProductUid|string $product): ?array
    {
        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->from(Product::class, 'product');

        $qb->where('product.id = :product')
            ->setParameter('product', $product, ProductUid::TYPE);


        $qb->leftJoin(
            'product',
            ProductCategory::class,
            'product_category',
            'product_category.event = product.event AND product_category.root = true'
        );


        $qb->leftJoin(
            'product',
            ProductInfo::class,
            'product_info',
            'product_info.product = product.id'
        );


        //$qb->addSelect('barcode.event');
        $qb->join(
            'product_category',
            WbBarcode::class,
            'barcode',
            'barcode.id = product_category.category AND barcode.profile = product_info.profile'
        );

        $qb->addSelect('barcode_event.offer');
        $qb->addSelect('barcode_event.variation');
        $qb->addSelect('barcode_event.counter');

        $qb->join(
            'barcode',
            WbBarcodeEvent::class,
            'barcode_event',
            'barcode_event.id = barcode.event'
        );

        return $qb
            ->enableCache('wildberries-products')
            ->fetchAssociative();
    }
}