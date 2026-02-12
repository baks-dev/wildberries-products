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

namespace BaksDev\Wildberries\Products\Api\Stocks;

use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Wildberries\Api\Wildberries;
use InvalidArgumentException;

final class UpdateWbFbsStocksRequest extends Wildberries
{
    private string $article;

    private string $barcode;

    private int $total = 0;

    /** Остаток */
    public function setTotal(int $total): self
    {
        $this->total = $total;
        return $this;
    }


    /** ID размера товара */
    private int|false $chrt = false;

    public function fromChrtId(int|string $chrt): self
    {
        $this->chrt = (int) $chrt;

        return $this;
    }

    /** Баркод */
    public function fromBarcode(ProductBarcode|string $barcode): self
    {
        if(true === ($barcode instanceof ProductBarcode))
        {
            $barcode = $barcode->getValue();
        }

        $this->barcode = $barcode;

        return $this;
    }


    public function setArticle(string $article): self
    {
        $this->article = $article;

        return $this;
    }

    /**
     * Метод обновляет количество остатков товаров продавца.
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products/#tag/Ostatki-na-skladah-prodavca/paths/~1api~1v3~1stocks~1{warehouseId}/put
     */
    public function update(): bool
    {
        /** Проверка, чтобы в тестовом окружении не изменялись данные */
        if(false === $this->isExecuteEnvironment())
        {
            return false;
        }

        if(empty($this->getWarehouse()))
        {
            throw new InvalidArgumentException('Invalid Argument warehouse');
        }

        if(empty($this->chrt))
        {
            throw new InvalidArgumentException('Invalid Argument Chrt');
        }

        if(false === $this->isStock())
        {
            return false;
        }

        if(false === $this->isSales())
        {
            $this->total = 0;
        }


        $response = $this
            ->marketplace()
            ->TokenHttpClient()
            ->request(
                method: 'PUT',
                url: sprintf('/api/v3/stocks/%s', $this->getWarehouse()),
                options: [
                    "json" => [
                        'stocks' => [
                            [
                                'chrtId' => $this->chrt,
                                'amount' => $this->total,
                            ],
                        ],
                    ],
                ],
            );

        if($response->getStatusCode() !== 204)
        {
            $content = $response->toArray(false);

            $this->logger->critical(
                sprintf(
                    'wildberries-products: Ошибка «%s» обновления остатков FBS карточки товара %s (%s)',
                    $response->getStatusCode(), $this->article, $this->barcode),
                [
                    self::class.':'.__LINE__,
                    $content,
                ]);

            if(current($content)['code'] === 'NotFound')
            {
                return true;
            }

            return false;
        }

        return true;
    }
}