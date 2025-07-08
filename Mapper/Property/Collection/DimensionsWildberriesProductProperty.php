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

use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.wb.product.property')]
final class DimensionsWildberriesProductProperty implements WildberriesProductPropertyInterface
{
    /**
     * Габариты упаковки товара. Указывать в сантиметрах для любого товара.
     *
     * length* Длина упаковки в см
     * width* Ширина упаковки в см
     * height* Высота упаковки в см.
     */
    public const string PARAM = 'dimensions';

    public function getIndex(): string
    {
        return self::PARAM;
    }

    public function required(): bool
    {
        return false;
    }

    public function default(): ?string
    {
        return 'false';
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
        return 300;
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


    public function getData(WildberriesProductsCardResult $data): ?array
    {
        if(
            false === empty($data->getLength()) &&
            false === empty($data->getWidth()) &&
            false === empty($data->getHeight()) &&
            false === empty($data->getWeight())
        )
        {
            return [
                'length' => (int) ($data->getLength() / 10),
                'width' => (int) ($data->getWidth() / 10),
                'height' => (int) ($data->getHeight() / 10),
            ];
        }

        return null;
    }
}
