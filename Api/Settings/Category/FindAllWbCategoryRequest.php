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

namespace BaksDev\Wildberries\Products\Api\Settings\Category;

use BaksDev\Wildberries\Api\Wildberries;
use DateInterval;
use Generator;
use Symfony\Contracts\Cache\ItemInterface;


final class FindAllWbCategoryRequest extends Wildberries
{

    private int|false $parent = false;

    public function parent(int|string $parent): self
    {
        $this->parent = (int) $parent;

        return $this;
    }

    public function findAll(): Generator|false
    {
        $cache = $this->getCacheInit('wildberries-products');
        $key = md5(self::class.$this->parent);
        // $cache->deleteItem($key);

        $content = $cache->get($key, function(ItemInterface $item): array|false {

            $item->expiresAfter(1);

            $response = $this->content()->TokenHttpClient()
                ->request('GET', '/content/v2/object/all',
                    $this->parent ? ['query' => [
                        'limit' => 1000,
                        'parentID' => $this->parent,
                    ]] : []);

            $content = $response->toArray(false);

            if($response->getStatusCode() !== 200)
            {
                $this->logger->critical('wildberries: Ошибка категории ', [$content, self::class.''.__LINE__]);

                return false;
            }

            if(empty($content['data']))
            {
                return false;
            }

            $item->expiresAfter(DateInterval::createFromDateString('1 day'));

            return $content['data'];

        });

        if(false === $content)
        {
            return false;
        }

        foreach($content as $data)
        {
            yield new WbCategoryDTO($data);
        }

    }


}