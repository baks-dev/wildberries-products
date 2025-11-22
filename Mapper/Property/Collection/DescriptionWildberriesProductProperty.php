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

namespace BaksDev\Wildberries\Products\Mapper\Property\Collection;

use BaksDev\Wildberries\Products\Mapper\Params\Collection\PurposeTireWildberriesProductParameters;
use BaksDev\Wildberries\Products\Mapper\Params\Collection\SeasonalityWildberriesProductParameters;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\PurposeYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\SeasonYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.wb.product.property')]
final class DescriptionWildberriesProductProperty implements WildberriesProductPropertyInterface
{
    /**
     * Подробное описание товара: например, его преимущества и особенности.
     */
    public const string PARAM = 'description';


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
        return 503;
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
        return false;
    }

    public function getData(WildberriesProductsCardResult $data): ?string
    {
        if($data->getMarketCategory() !== WildberriesProductProperty::CATEGORY_TIRE)
        {
            return null;
        }

        $name = 'Шины ';

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
                        $name .= $season_value.' ';
                    }
                }
            }
        }


        /** Приводим к нижнему регистру и первой заглавной букве */
        $name = mb_strtolower(trim($name));
        $firstChar = mb_substr($name, 0, 1, 'UTF-8');
        $then = mb_substr($name, 1, null, 'UTF-8');
        $name = mb_strtoupper($firstChar, 'UTF-8').$then.' ';


        $name .= $data->getProductName().' ';

        if($data->getProductVariationValue())
        {
            $name .= $data->getProductVariationValue();
        }

        if($data->getProductModificationValue())
        {
            $name .= '/'.$data->getProductModificationValue().' ';
        }

        if($data->getProductOfferValue())
        {
            $name .= 'R'.$data->getProductOfferValue().' ';
        }

        if($data->getProductOfferPostfix())
        {
            $name .= $data->getProductOfferPostfix().' ';
        }

        if($data->getProductVariationPostfix())
        {
            $name .= $data->getProductVariationPostfix().' ';
        }

        if($data->getProductModificationPostfix())
        {
            $name .= $data->getProductModificationPostfix().' ';
        }

        if($data->getProductParams() !== false)
        {
            /** Добавляем к названию назначение */
            $Purpose = new PurposeTireWildberriesProductParameters();

            foreach($data->getProductParams() as $product_param)
            {
                if($Purpose->equals($product_param->name))
                {
                    $purpose_value = $Purpose->getData($data);

                    if(false === empty($purpose_value['value']))
                    {
                        $name .= $purpose_value['value'].' ';
                    }
                }
            }
        }

        $name .= PHP_EOL.PHP_EOL;
        $name .= $data->getProductPreview();

        if($data->getProductVariationValue())
        {
            $name .= PHP_EOL.PHP_EOL;
            $name .= sprintf('Ширина профиля %s обеспечивает надежное сцепление с дорогой и стабильность на поворотах.',
                $data->getProductVariationValue(),
            );
        }

        if($data->getProductModificationValue())
        {
            $name .= PHP_EOL.PHP_EOL;
            $name .= sprintf('Высота профиля %s обеспечивает оптимальное сочетание комфорта и управляемости.',
                $data->getProductModificationValue());
        }

        return empty($name) ? null : trim(strip_tags($name));

    }
}
