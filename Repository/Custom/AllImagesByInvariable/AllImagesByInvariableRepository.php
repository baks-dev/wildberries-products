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

namespace BaksDev\Wildberries\Products\Repository\Custom\AllImagesByInvariable;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Wildberries\Products\Entity\Custom\Images\WildberriesProductCustomImage;
use BaksDev\Wildberries\Products\Entity\Custom\WildberriesProductCustom;


final readonly class AllImagesByInvariableRepository implements AllImagesByInvariableInterface
{
    public function __construct(private DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод возвращает все кастомные изображения продукта
     */
    public function findAll(ProductInvariableUid $invariable): array|bool
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(WildberriesProductCustom::class, 'wb_product_custom')
            ->where('wb_product_custom.invariable = :invariable')
            ->setParameter(
                key: 'invariable',
                value: $invariable,
                type: ProductInvariableUid::TYPE,
            );

        $dbal
            ->addSelect("CONCAT ('/upload/".$dbal->table(WildberriesProductCustomImage::class)."' , '/', wb_product_custom_images.name) AS product_image")
            /** Расширение файла */
            ->addSelect("wb_product_custom_images.ext AS product_image_ext")
            /** Флаг загрузки файла CDN */
            ->addSelect("wb_product_custom_images.cdn AS product_image_cdn")
            ->leftJoin(
                'wb_product_custom',
                WildberriesProductCustomImage::class,
                'wb_product_custom_images',
                'wb_product_custom_images.invariable = wb_product_custom.invariable',
            );

        $dbal->orderBy('wb_product_custom_images.root', 'DESC');

        return $dbal
            ->enableCache('wildberries-products', '1 day')
            ->fetchAllAssociative();
    }
}