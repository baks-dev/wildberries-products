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

namespace BaksDev\Wildberries\Products\Api\Cards;

use BaksDev\Reference\Money\Type\Money;
use BaksDev\Wildberries\Api\Wildberries;

final class UpdateGroupWildberriesProductCardsRequest extends Wildberries
{
    /**
     * Объединение и разъединение карточек товаров
     *
     * Метод объединяет и разъединяет карточки товаров. Карточки товаров считаются объединёнными, если у них одинаковый
     * imtID.
     *
     * Для объединения карточек товаров сделайте запрос с указанием imtID. Можно объединять не более 30 карточек
     * товаров. Для разъединения карточек товаров сделайте запрос без указания imtID. Для разъединенных карточек будут
     * сгенерированы новые imtID.
     *
     * Если вы разъедините одновременно несколько карточек товаров, эти карточки объединятся в одну и получат новый
     * imtID. Чтобы присвоить каждой карточке товара уникальный imtID, необходимо передавать по одной карточке товара
     * за запрос.
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products#tag/Kartochki-tovarov/paths/~1content~1v2~1cards~1update/post
     */

    private int $nomenclature;

    /** Идентификатор */
    private int $group;

    /** Артикул WB */
    public function nomenclature(int $nomenclature): self
    {
        $this->nomenclature = $nomenclature;

        return $this;
    }

    /** Цена */
    public function group(int $group): self
    {
        $this->group = $group;

        return $this;
    }

    public function update(): bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        if(false === $this->isCard())
        {
            return true;
        }

        $response = $this->content()->TokenHttpClient()->request(
            'POST',
            '/content/v2/cards/moveNm',
            ['json' => [
                'targetIMT' => $this->group,
                'nmIDs' => [$this->nomenclature],
            ]],
        );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            // Указанные цены и скидки уже установлены
            if($content['errorText'] === 'The specified prices and discounts are already set')
            {
                return true;
            }

            $this->logger->critical(sprintf('wildberries-products: %s (%s)',
                $response->getStatusCode(),
                $content['statusText'],
            ), [self::class.':'.__LINE__]);

            return false;
        }

        return true;
    }
}