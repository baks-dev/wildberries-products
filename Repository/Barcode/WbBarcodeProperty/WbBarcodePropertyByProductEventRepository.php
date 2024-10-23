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

namespace BaksDev\Wildberries\Products\Repository\Barcode\WbBarcodeProperty;

use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Custom\WbBarcodeCustom;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Barcode\Property\WbBarcodeProperty;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;
use Doctrine\DBAL\Connection;

final class WbBarcodePropertyByProductEventRepository implements WbBarcodePropertyByProductEventInterface
{

    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    public function getPropertyCollection(ProductEvent|ProductEventUid|string $event): array
    {
        if(is_string($event))
        {
            $event = new ProductEventUid($event);
        }

        if($event instanceof ProductEvent)
        {
            $event = $event->getId();
        }


        $property = $this->getProperty();
        $custom = $this->getCustom();

        $qb = $this->connection->prepare($property.' UNION '.$custom.' ORDER BY sort ');
        $qb->bindValue('event', $event, ProductEventUid::TYPE);


        return $qb->executeQuery()->fetchAllAssociative();
    }


    private function getProperty(): string
    {
        $qb = $this->from();

        $qb->addSelect('sticker_settings_property.sort');
        $qb->addSelect('sticker_settings_property.name');

        $qb->join(
            'sticker_settings_event',
            WbBarcodeProperty::TABLE,
            'sticker_settings_property',
            'sticker_settings_property.event = sticker_settings_event.id'
        );

        $qb->addSelect('product_property.value');

        $qb->join(
            'sticker_settings_property',
            ProductProperty::TABLE,
            'product_property',
            'product_property.event = product_event.id AND product_property.field = sticker_settings_property.offer');


        return $qb->getSQL();
    }


    private function getCustom(): string
    {
        $qb = $this->from();

        $qb->addSelect('sticker_settings_custom.sort');
        $qb->addSelect('sticker_settings_custom.name');
        $qb->addSelect('sticker_settings_custom.value');
        $qb->join(
            'sticker_settings_event',
            WbBarcodeCustom::TABLE,
            'sticker_settings_custom',
            'sticker_settings_custom.event = sticker_settings_event.id'
        );

        return $qb->getSQL();
    }


    private function from()
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->from(ProductEvent::TABLE, 'product_event');

        /* Категория продукта */
        //$qb->select('product_event_category.category');


        $qb->leftJoin(
            'product_event',
            ProductInfo::TABLE,
            'product_info',
            'product_info.product = product_event.main'
        );


        $qb->leftJoin(
            'product_event',
            ProductCategory::TABLE,
            'product_event_category',
            'product_event_category.event = product_event.id AND product_event_category.root = true'
        );


        $qb->join(
            'product_event',
            WbBarcode::TABLE,
            'sticker_settings',
            'sticker_settings.id = product_event_category.category AND sticker_settings.profile = product_info.profile'
        );

        $qb->addSelect('sticker_settings_event.offer');
        $qb->addSelect('sticker_settings_event.counter');
        $qb->join(
            'sticker_settings',
            WbBarcodeEvent::TABLE,
            'sticker_settings_event',
            'sticker_settings_event.id = sticker_settings.event'
        );

        $qb->where('product_event.id = :event');

        return $qb;
    }

}