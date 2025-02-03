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

namespace BaksDev\Wildberries\Products\Repository\Settings\ProductSettingsCategoryParameters;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Wildberries\Products\Entity\Settings\Invariable\WbProductSettingsInvariable;
use BaksDev\Wildberries\Products\Entity\Settings\Parameters\WbProductSettingsParameters;
use BaksDev\Wildberries\Products\Entity\Settings\WbProductSettings;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;


final class ProductSettingsCategoryParametersRepository implements ProductSettingsCategoryParametersInterface
{
    private int|false $category = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function category(int $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function find(int $type): CategoryProductSectionFieldUid|false
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
                WbProductSettingsParameters::class,
                'parameters',
                'parameters.event = settings.event AND parameters.type = :type'
            )
            ->setParameter('type', $type, Types::INTEGER);


        $dbal->select('parameters.field AS value');
        $dbal->addSelect('parameters.type AS const');

        return $dbal
            // ->enableCache('Namespace', 3600)
            ->fetchHydrate(CategoryProductSectionFieldUid::class);
    }
}