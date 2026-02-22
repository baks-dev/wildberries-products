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

namespace BaksDev\Wildberries\Products\Mapper\Property\Collection\Tire;

use BaksDev\Wildberries\Products\Mapper\Params\Collection\SeasonalityWildberriesProductParameters;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.wb.product.property')]
final class TitleWildberriesProductProperty implements WildberriesProductPropertyInterface
{
    /**
     * Наименование товара
     */

    /** @see WildberriesProductProperty */
    public const array CATEGORY = [
        WildberriesProductProperty::CATEGORY_TIRE,
    ];

    public const string PARAM = 'title';

    public function getIndex(): string
    {
        return self::PARAM;
    }

    public function default(): ?string
    {
        return null;
    }

    public function required(): bool
    {
        return false;
    }

    public function choices(): ?array
    {
        return null;
    }

    /**
     * Сортировка (чем меньше число - тем первым в итерации будет значение)
     */
    public static function priority(): int
    {
        return 999;
    }

    /**
     * Проверяет, относится ли статус к данному объекту
     */
    public static function equals(string $param): bool
    {
        return self::PARAM === $param;
    }


    public function isSetting(): bool
    {
        return true;
    }

    public function isCard(): bool
    {
        return true;
    }

    public function getData(WildberriesProductsCardResult $data): ?string
    {
        if(true === empty($data->getMarketCategory()) || $data->getMarketCategory() !== WildberriesProductProperty::CATEGORY_TIRE)
        {
            return null;
        }

        $name = 'Шины ';


        //        /** Приводим к нижнему регистру и первой заглавной букве */
        //        $name = mb_strtolower(trim($name));
        //        $firstChar = mb_substr($name, 0, 1, 'UTF-8');
        //        $then = mb_substr($name, 1, null, 'UTF-8');
        //        $name = mb_strtoupper($firstChar, 'UTF-8').$then.' ';


        if(false === empty($data->getProductVariationValue()))
        {
            $name .= $data->getProductVariationValue();
        }

        if(false === empty($data->getProductModificationValue()))
        {
            $name .= '/'.$data->getProductModificationValue().' ';
        }

        if(false === empty($data->getProductOfferValue()))
        {
            $name .= 'R'.$data->getProductOfferValue().' ';
        }

        if(false === empty($data->getProductOfferPostfix()))
        {
            $name .= $data->getProductOfferPostfix().' ';
        }

        if(false === empty($data->getProductVariationPostfix()))
        {
            $name .= $data->getProductVariationPostfix().' ';
        }

        if($data->getProductModificationPostfix())
        {
            $name .= $data->getProductModificationPostfix().' ';
        }

        if($data->getProductParams() !== false)
        {
            /** Добавляем к названию сезонность */
            $Season = new SeasonalityWildberriesProductParameters();

            foreach($data->getProductParams() as $product_param)
            {
                if($Season->equals($product_param->name))
                {
                    $season_value = $Season->getData($data);

                    $season_value = match ($season_value['value'])
                    {
                        'лето', 'summer' => 'летние',
                        'зима', 'winter' => 'зимние',
                        'всесезонные', 'all' => 'всесезонные',
                        default => '',
                    };

                    if(false === empty($season_value))
                    {
                        $name .= mb_strlen($name.$season_value) > 60 ? '' : $season_value.' ';
                    }
                }
            }
        }

        $name .= $data->getModelName().' ';


        return empty($name) ? null : trim($name);
    }
}