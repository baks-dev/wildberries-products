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

namespace BaksDev\Wildberries\Products\Repository\Settings\ProductSettingsCategoryProperty;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Wildberries\Products\Entity\Settings\Invariable\WbProductSettingsInvariable;
use BaksDev\Wildberries\Products\Entity\Settings\Property\WbProductSettingsProperty;
use BaksDev\Wildberries\Products\Entity\Settings\WbProductSettings;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;


final class ProductSettingsCategoryPropertyRepository implements ProductSettingsCategoryPropertyInterface
{
    private int|false $category = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function category(int $category): self
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Метод возвращает идентификатор свойства карточки по его идентификатору Wildberries
     */
    public function find(string $type): CategoryProductSectionFieldUid|false
    {
        if(false === $this->category)
        {
            throw new InvalidArgumentException('Invalid Argument Category');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(WbProductSettingsInvariable::class, 'invariable')
            ->where('invariable.category = :category')
            ->setParameter('category', $this->category, Types::INTEGER);

        $dbal
            ->join(
                'invariable',
                WbProductSettings::class,
                'settings',
                'settings.id = invariable.main'
            );

        $dbal
            ->join(
                'invariable',
                WbProductSettingsProperty::class,
                'property',
                'property.event = settings.event AND property.type = :type'
            )
            ->setParameter(
                'type',
                $type,
                WildberriesProductProperty::TYPE
            );

        /** Определяем тип свойства, не является ли оно справочником */

        $dbal
            ->leftJoin(
                'invariable',
                CategoryProduct::class,
                'category',
                'category.id = invariable.main'
            );

        $dbal
            ->leftJoin(
                'category',
                CategoryProductSection::class,
                'section',
                'section.event = category.event'
            );


        $dbal
            ->leftJoin(
                'section',
                CategoryProductSectionField::class,
                'field',
                'field.section = section.id AND field.const = property.field'
            );


        $dbal->select('property.field AS value');
        $dbal->addSelect('property.type AS const');
        $dbal->addSelect('field.type AS attr');

        return $dbal
            // ->enableCache('Namespace', 3600)
            ->fetchHydrate(CategoryProductSectionFieldUid::class);
    }
}