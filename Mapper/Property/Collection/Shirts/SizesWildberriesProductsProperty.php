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

use BaksDev\Reference\Clothing\Type\SizeClothing;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('baks.wb.product.property')]
final class SizesWildberriesProductsProperty implements WildberriesProductPropertyInterface
{
    /**
     * Артикул продавца
     */

    /** @see WildberriesProductProperty */
    public const array CATEGORY = [
        WildberriesProductProperty::CATEGORY_SHIRTS,
    ];

    public const string PARAM = 'sizes';

    public function __construct(private readonly FindAllWildberriesCardsRequest $FindAllWildberriesCardsRequest) {}

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
    public function getData(WildberriesProductsCardResult $data): array|false
    {
        if(false === class_exists(SizeClothing::class))
        {
            return false;
        }

        $sizes = $data->getProductSize();

        if(false !== $data->getProductSize())
        {
            if(empty($sizes))
            {
                return false;
            }

            $size = [];

            $card = $this->FindAllWildberriesCardsRequest
                ->profile($data->getProfile())
                ->findAll($data->getSearchArticle());

            /** При обновлении существующей карточки */
            if(false !== $card)
            {
                $card = $card->current();

                /** @var WildberriesCardDTO $card */
                foreach($sizes as $item)
                {
                    $value = new SizeClothing($item->value)->getSize();

                    $price = new Money($item->price, true);

                    $size[] = [
                        'chrtID' => $card->getChrt($item->value),
                        'techSize' => $value->getValue(),
                        'wbSize' => $value->getRus(), // Российский размер товара
                        'price' => $price->getRoundValue(), // Российский размер товара
                        'skus' => [$item->barcode], // Российский размер товара
                    ];
                }

                return $size;
            }

            /** При создании новой карточки */
            foreach($sizes as $item)
            {
                $value = new SizeClothing($item->value)->getSize();

                $price = new Money($item->price, true);

                $size[] = [
                    'techSize' => $value->getValue(),
                    'wbSize' => $value->getRus(), // Российский размер товара
                    'price' => $price->getRoundValue(), // Российский размер товара
                    'skus' => [$item->barcode], // Российский размер товара
                ];

                return $size;
            }
        }

        return false;
    }
}