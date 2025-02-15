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

namespace BaksDev\Wildberries\Products\Repository\Barcode\WbBarcodeSettings;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Custom\WbBarcodeCustom;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Barcode\Property\WbBarcodeProperty;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;
use InvalidArgumentException;

final class WbBarcodeSettingsRepository implements WbBarcodeSettingsInterface
{
    private ProductUid|false $product = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function forProduct(Product|ProductUid|string $product): self
    {

        if(is_string($product))
        {
            $product = new ProductUid($product);
        }

        if($product instanceof Product)
        {
            $product = $product->getId();
        }

        $this->product = $product;

        return $this;
    }


    private function builder(): DBALQueryBuilder
    {
        if(false === ($this->product instanceof ProductUid))
        {
            throw new InvalidArgumentException('Invalid Argument Product');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(Product::class, 'product');

        $dbal->where('product.id = :product')
            ->setParameter(
                key: 'product',
                value: $this->product,
                type: ProductUid::TYPE);


        $dbal->leftJoin(
            'product',
            ProductCategory::class,
            'product_category',
            'product_category.event = product.event AND product_category.root = true'
        );

        $dbal->leftJoin(
            'product',
            ProductInfo::class,
            'product_info',
            'product_info.product = product.id'
        );

        //$dbal->addSelect('barcode.event');
        $dbal->join(
            'product_category',
            WbBarcode::class,
            'barcode',
            'barcode.id = product_category.category AND barcode.profile = product_info.profile'
        );

        $dbal->addSelect('barcode_event.offer');
        $dbal->addSelect('barcode_event.variation');
        $dbal->addSelect('barcode_event.modification');
        $dbal->addSelect('barcode_event.counter');

        $dbal->join(
            'barcode',
            WbBarcodeEvent::class,
            'barcode_event',
            'barcode_event.id = barcode.event'
        );

        /** Получаем настройки свойств */

        $dbal->leftJoin(
            'barcode',
            WbBarcodeProperty::class,
            'property',
            'property.event = barcode.event'
        );

        $dbal->leftJoin(
            'property',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event AND product_property.field = property.offer');


        $dbal->addSelect("JSON_AGG ( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'0', property.sort,
						'name', property.name,
						'value', product_property.value
					)
					
			) 
			 FILTER (WHERE product_property.value IS NOT NULL) 
            AS property
		");


        /** Получаем пользовательские свойства */

        $dbal->leftJoin(
            'barcode',
            WbBarcodeCustom::class,
            'custom',
            'custom.event = barcode.event'
        );


        $dbal->addSelect("JSON_AGG ( DISTINCT
				
					JSONB_BUILD_OBJECT
					(
						'0', custom.sort,
						'name', custom.name,
						'value', custom.value
					)
					
			) 
			 FILTER (WHERE custom.value IS NOT NULL) 
            AS custom
		");


        $dbal->allGroupByExclude();

        return $dbal;
    }

    /**
     * @deprecated  ->forProduct()->find()
     */
    public function findWbBarcodeSettings(): array|false
    {
        $dbal = $this->builder();

        return $dbal
            ->enableCache('wildberries-products')
            ->fetchAssociative();
    }

    public function find(): WbBarcodeSettingsResult|false
    {
        $dbal = $this->builder();


        return $dbal
            ->enableCache('wildberries-products')
            ->fetchHydrate(WbBarcodeSettingsResult::class);
    }

}