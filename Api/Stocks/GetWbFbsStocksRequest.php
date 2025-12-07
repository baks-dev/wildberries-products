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

namespace BaksDev\Wildberries\Products\Api\Stocks;

use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Wildberries\Api\Wildberries;
use InvalidArgumentException;

final class GetWbFbsStocksRequest extends Wildberries
{
    private string|false $barcode = false;

    /** Баркод */
    public function fromBarcode(ProductBarcode|string $barcode): self
    {
        if($barcode instanceof ProductBarcode)
        {
            $barcode = $barcode->getValue();
        }

        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Получить остатки товаров
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products#tag/Ostatki-na-skladah-prodavca/paths/~1api~1v3~1stocks~1%7BwarehouseId%7D/post
     */
    public function find(): int|bool
    {
        /** Если запрещено обновление остатков - возвращаем TRUE */
        if(false === $this->isStock())
        {
            return true;
        }

        if(empty($this->barcode))
        {
            throw new InvalidArgumentException('Invalid Argument barcode');
        }

        if(empty($this->getWarehouse()))
        {
            throw new InvalidArgumentException('Invalid Argument warehouse');
        }

        $response = $this
            ->marketplace()
            ->TokenHttpClient()
            ->request(
                method: 'POST',
                url: '/api/v3/stocks/'.$this->getWarehouse(),
                options: [
                    "json" => [
                        'skus' => [
                            $this->barcode,
                        ],
                    ],
                ],
            );


        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical(
                sprintf('wildberries-manufacture: Ошибка при получении остатков FBS'),
                [
                    self::class.':'.__LINE__,
                    $content,
                ]);

            return false;
        }

        if(empty($content['stocks']))
        {
            return 0;
        }

        $stock = array_filter($content['stocks'], static fn($v) => $v['sku'] === $this->barcode);

        if(empty($stock))
        {
            return 0;
        }

        return current($stock)['amount'];
    }
}