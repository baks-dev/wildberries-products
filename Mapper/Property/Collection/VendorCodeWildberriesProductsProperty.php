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

use BaksDev\Ozon\Products\Mapper\Property\OzonProductsPropertyInterface;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.wb.product.property')]
final class VendorCodeWildberriesProductsProperty implements WildberriesProductPropertyInterface
{
    /**
     * Артикул продавца
     */

    public const string PARAM = 'vendorCode';

    public function getIndex(): string
    {
        return self::PARAM;
    }

    /**
     * Возвращает значение по умолчанию
     */
    public function default(): ?string
    {
        return null;
    }

    /**
     * Метод указывает, нужно ли добавить свойство для заполнения в форму
     */
    public function isSetting(): bool
    {
        return false;
    }


    public function required(): bool
    {
        return true;
    }

    public static function priority(): int
    {
        return 500;
    }

    /**
     * Проверяет, относится ли значение к данному объекту
     */
    public static function equals(string $param): bool
    {
        return self::PARAM === $param;
    }

    public function choices(): ?array
    {
        return null;
    }

    /**
     * Возвращает состояние
     */
    public function getData(array $data): string|false
    {
        return $data['article'] ?? false;
    }
}
