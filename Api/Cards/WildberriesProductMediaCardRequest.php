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

use BaksDev\Wildberries\Api\Wildberries;

final class WildberriesProductMediaCardRequest extends Wildberries
{


    /**
     * Загрузить медиафайлы по ссылкам
     *
     * Требования к ссылкам:
     *
     * ссылка должна вести прямо на файл. Убедитесь, что ссылка не ведёт на страницу предпросмотра или авторизации,
     * например. Если по ссылке открывается текстовая страница TXT или HTML, ссылка считается некорректной для доступа
     * к файлу по ссылке не нужна авторизация
     *
     * Требования к изображениям:
     *
     * максимум изображений для одной карточки товара — 30
     * минимальное разрешение — 700×900 px
     * максимальный размер — 32 Мб
     * минимальное качество — 65%
     * форматы — JPG, PNG, BMP, GIF (статичные), WebP
     *
     * Требования к видео:
     *
     * максимум одно видео для одной карточки товара
     * максимальный размер — 50 Мб
     * форматы — MOV, MP4
     *
     * @see https://dev.wildberries.ru/openapi/work-with-products#tag/Mediafajly/paths/~1content~1v3~1media~1save/post
     */

    private int $nomenclature;

    public function nomenclature(int $nomenclature): self
    {
        $this->nomenclature = $nomenclature;

        return $this;
    }

    public function update(array $media): bool
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

        $response = $this
            ->content()
            ->TokenHttpClient()
            ->request(
                'POST',
                '/content/v3/media/save',
                ['json' => [
                    'nmId' => $this->nomenclature,
                    'data' => $media,
                ]],
            );

        $content = $response->toArray(false);

        if($response->getStatusCode() !== 200)
        {
            $this->logger->critical(sprintf('wildberries-products: %s', $response->getStatusCode(),
            ), [self::class.':'.__LINE__, $content]);

            return false;
        }

        return true;
    }
}