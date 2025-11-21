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

namespace BaksDev\Wildberries\Products\Api\Cards;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Wildberries\Api\Wildberries;

final class UpdateWildberriesProductPriceRequest extends Wildberries
{
    /**
     * Установить цены для размеров
     *
     * Метод устанавливает цены и скидки для товаров.
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products#tag/Ceny-i-skidki/paths/~1api~1v2~1upload~1task/post
     */

    private int $nomenclature;

    private int $price;

    /** Артикул WB */
    public function nomenclature(int $nomenclature): self
    {
        $this->nomenclature = $nomenclature;

        return $this;
    }

    /** Цена */
    public function price(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function update(): bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        /** Инициируем токен для вызова параметров */
        $TokenHttpClient = $this->discountsPrices()->TokenHttpClient();

        /** Применяем к стоимости надбавку токена */
        $price = new Money($this->price)
            ->applyString($this->getPercent());

        $response = $TokenHttpClient->request(
            'POST',
            '/api/v2/upload/task',
            ['json' => ['data' =>
                [
                    [
                        'nmID' => $this->nomenclature,
                        'price' => $price->getRoundValue(),
                        'discount' => 0,
                    ],
                ],
            ]],
        );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            if($response->getStatusCode() === 400 || $response->getStatusCode() === 403)
            {
                $this->logger->critical(sprintf('wildberries-products: %s (%s)',
                    $response->getStatusCode(),
                    $content['errorText'],
                ), [self::class.':'.__LINE__]);

                return false;
            }

            $this->logger->critical(sprintf('wildberries-products: %s (%s)',
                $content['status'],
                $content['statusText'],
            ), [self::class.':'.__LINE__]);

            return false;
        }

        return true;
    }
}