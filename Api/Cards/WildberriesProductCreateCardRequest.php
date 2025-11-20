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

final class WildberriesProductCreateCardRequest extends Wildberries
{
    /**
     * Метод создаёт карточки товаров c указанием описаний и характеристик товаров.
     *
     * Есть две формы запроса: для создания отдельных и объединённых карточек товаров.
     * Габариты товаров можно указать только в сантиметрах, вес товара с упаковкой — в килограммах.
     *
     * Создание карточки товара происходит асинхронно. После отправки запрос становится в очередь на обработку.
     * В одном запросе можно создать максимум 100 объединённых карточек товаров (imtID), по 30 карточек товаров в
     * каждой. Максимальный размер запроса 10 Мб.
     *
     * Если ответ Успешно (200), но какие-то карточки не обновились, можно получить список несозданных карточек
     * товаров.
     * https://content-api.wildberries.ru/content/v2/cards/error/list
     *
     * Лимит — 10 запросов в минуту на один аккаунт продавца
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products/#tag/Sozdanie-kartochek-tovarov/paths/~1content~1v2~1cards~1upload/post
     */

    public function create(array $card): bool
    {
        if($this->isExecuteEnvironment() === false)
        {
            $this->logger->critical('Запрос может быть выполнен только в PROD окружении', [self::class.':'.__LINE__]);
            return true;
        }

        /** Инициируем токен для вызова параметров */
        $TokenHttpClient = $this->content()->TokenHttpClient();

        /** Обновляем стоимость всех размеров согласно настройке токена */
        foreach($card['sizes'] as $i => $size)
        {
            $price = new Money($size['price'])
                ->applyString($this->getPercent());

            $card['sizes'][$i]['price'] = $price->getRoundValue();
        }

        $response = $TokenHttpClient->request(
            'POST',
            '/content/v2/cards/upload',
            ['json' => [$card]],
        );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            if($response->getStatusCode() === 400)
            {
                $this->logger->critical(sprintf('wildberries-products: %s (%s)',
                    $response->getStatusCode(),
                    $content['errorText'],
                ), [self::class.':'.__LINE__, $card]);

                return false;
            }

            $this->logger->critical(sprintf('wildberries-products: %s (%s)',
                $content['status'],
                $content['statusText'],
            ), [self::class.':'.__LINE__, $card]);

            return false;
        }

        return true;
    }
}