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

use BaksDev\Wildberries\Api\Wildberries;
use DateInterval;
use DateTimeImmutable;
use DateTimeInterface;
use Generator;
use Symfony\Contracts\Cache\ItemInterface;

final class FindAllWildberriesCardsRequest extends Wildberries
{
    private const int LIMIT = 100;

    private ?DateTimeImmutable $updated = null;

    private ?int $nomenclature = null;

    /**
     * @return array{WildberriesCardDTO}
     *
     * https://dev.wildberries.ru/openapi/work-with-products/#tag/Kartochki-tovarov/paths/~1content~1v2~1get~1cards~1list/post
     */
    public function findAll(string|null|false $search = null): Generator|false
    {
        while(true)
        {
            $cache = $this->getCacheInit('wildberries-products');
            $key = md5(self::class.$this->getProfile().$this->nomenclature.$search);
            // $cache->deleteItem($key);

            $content = $cache->get($key, function(ItemInterface $item) use ($search) {

                $item->expiresAfter(DateInterval::createFromDateString('1 seconds'));

                $json = [
                    "settings" => [
                        "cursor" => [
                            "limit" => self::LIMIT,
                            "updatedAt" => $this->updated?->format(DateTimeInterface::W3C),
                            "nmID" => $this->nomenclature,

                        ],
                        "filter" => [
                            "textSearch" => $search ?: '',
                            "withPhoto" => 1,
                        ],
                    ]
                ];

                $response = $this->content()->TokenHttpClient()
                    ->request('POST', '/content/v2/get/cards/list',
                        ['json' => $json]);

                $content = $response->toArray(false);

                if($response->getStatusCode() !== 200)
                {
                    $this->logger->critical('wildberries: Ошибка списка карточек ', [$content, self::class.''.__LINE__]);
                    return false;
                }

                $item->expiresAfter(DateInterval::createFromDateString('1 hours'));

                return $response->toArray(false);

            });


            if(empty($content['cursor']))
            {
                break;
            }

            $cursor = $content['cursor'];

            foreach($content['cards'] as $data)
            {
                yield new WildberriesCardDTO($data, $this->getProfile());
            }

            if(self::LIMIT > $cursor['total'])
            {
                break;
            }

            $this->updated = new DateTimeImmutable($cursor['updatedAt']);
            $this->nomenclature = $cursor['nmID'];

        }
    }
}