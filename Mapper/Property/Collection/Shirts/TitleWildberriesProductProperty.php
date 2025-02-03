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

namespace BaksDev\Wildberries\Products\Mapper\Property\Collection\Shirts;

use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\PurposeYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Mapper\Params\Tire\SeasonYaMarketProductParams;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.wb.product.property')]
final class TitleWildberriesProductProperty implements WildberriesProductPropertyInterface
{
    /**
     * Наименование товара
     */

    public const int CATEGORY = 192;

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
        return false;
    }

    public function isCard(): bool
    {
        return true;
    }

    public function getData(array $data): mixed
    {


        if(!isset($data['market_category']) || $data['market_category'] !== WildberriesProductProperty::CATEGORY_SHIRTS)
        {
            return null;
        }

        $name = 'Футболка';

        /** Приводим к нижнему регистру и первой заглавной букве */
        $name = mb_strtolower(trim($name));
        $firstChar = mb_substr($name, 0, 1, 'UTF-8');
        $then = mb_substr($name, 1, null, 'UTF-8');
        $name = mb_strtoupper($firstChar, 'UTF-8').$then;


        $name = trim($name).' '.$data['product_name'];

        if(!empty($data['product_variation_value']))
        {
            $name = trim($name).' '.$data['product_variation_value'];
        }

        if(!empty($data['product_modification_value']))
        {
            $name = trim($name).' '.$data['product_modification_value'];
        }

        if(!empty($data['product_offer_value']))
        {
            $name = trim($name).' '.$data['product_offer_value'];
        }

        if(!empty($data['product_offer_postfix']))
        {
            $name = trim($name).' '.$data['product_offer_postfix'];
        }

        if(!empty($data['product_variation_postfix']))
        {
            $name = trim($name).' '.$data['product_variation_postfix'];
        }

        if(!empty($data['product_modification_postfix']))
        {
            $name = trim($name).' '.$data['product_modification_postfix'];
        }


        return empty($name) ? null : trim($name);
    }
}