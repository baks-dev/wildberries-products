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

namespace BaksDev\Wildberries\Products\Api\Settings\Characteristic;

use BaksDev\Wildberries\Api\Token\Reference\Characteristics\WbCharacteristicByObjectNameDTO;
use BaksDev\Wildberries\Api\Wildberries;
use Generator;
use InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class WbCharacteristicRequest extends Wildberries
{
    private int|false $category = false;

    public function category(int|string $category): self
    {
        $this->category = (int) $category;
        return $this;
    }

    /**
     * Характеристики для создания КТ по всем подкатегориям
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products/#tag/Kategorii-predmety-i-harakteristiki/paths/~1content~1v2~1object~1charcs~1{subjectId}/get
     *
     * С помощью данного метода можно получить список характеристик, которые можно или нужно заполнить при создании КТ
     *     в подкатегории определенной родительской категории.
     */
    public function findAll(): Generator|false
    {

        if(false === $this->category)
        {
            throw new InvalidArgumentException('Invalid Argument category');
        }


        $key = md5(self::class.$this->category);
        $cache = new FilesystemAdapter('wildberries');

        /**
         * Кешируем результат запроса
         *
         * @var  ResponseInterface $response
         */
        $content = $cache->get($key, function(ItemInterface $item): array|false {
            $item->expiresAfter(1);

            $response = $this->content()->TokenHttpClient()
                ->request(
                    'GET',
                    sprintf('/content/v2/object/charcs/%s', $this->category),
                );

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical('wildberries-products: Ошибка характеристик', [
                    $content, self::class.':'.__LINE__
                ]);

                return false;
            }

            if(empty($content['data']))
            {
                return false;
            }

            $item->expiresAfter(86400);

            return $content['data'];
        });

        if(false === $content)
        {
            return false;
        }

        foreach($content as $data)
        {
            yield new WbCharacteristicDTO($data);
        }
    }

}