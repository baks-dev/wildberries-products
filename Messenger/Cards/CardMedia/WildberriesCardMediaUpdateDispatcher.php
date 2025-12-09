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

namespace BaksDev\Wildberries\Products\Messenger\Cards\CardMedia;


use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesProductMediaCardRequest;
use BaksDev\Wildberries\Products\Repository\Cards\WildberriesProductImages\WildberriesProductImagesInterface;
use BaksDev\Wildberries\Products\Repository\Custom\AllImagesByInvariable\AllImagesByInvariableInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляем файлы изображений после обновления карточки
 */
#[AsMessageHandler(priority: 0)]
final readonly class WildberriesCardMediaUpdateDispatcher
{
    public function __construct(
        #[Target('wildberriesProductsLogger')] private LoggerInterface $logger,
        private WildberriesProductMediaCardRequest $WildberriesProductMediaCardRequest,
        private FindAllWildberriesCardsRequest $FindAllWildberriesCardsRequest,
        private WildberriesProductImagesInterface $WildberriesProductImagesInterface,
        private AllImagesByInvariableInterface $AllImagesByInvariableRepository,
        #[Autowire(env: 'CDN_HOST')] private string $cdnHost,
        #[Autowire(env: 'HOST')] private string $host,
    ) {}


    public function __invoke(WildberriesCardMediaUpdateMessage $message): void
    {
        $result = $this->WildberriesProductImagesInterface
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->findAll();

        if(empty($result))
        {
            return;
        }

        /** Получаем текущее состояние карточки Wildberries */

        $wbCard = $this->FindAllWildberriesCardsRequest
            ->forTokenIdentifier($message->getIdentifier())
            ->findAll($message->getArticle());


        if(false === $wbCard || false === $wbCard->valid())
        {
            $this->logger->warning(
                sprintf('%s: Карточка товара Wildberries не найдена по артикулу',
                    $message->getArticle()),
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }


        /** @var WildberriesCardDTO $WildberriesCardDTO */
        $WildberriesCardDTO = $wbCard->current();

        $images = null;


        /** Первыми отправляем кастомные фото */

        $customs = $this->AllImagesByInvariableRepository->findAll($message->getInvariable());

        foreach($customs as $custom)
        {
            if($custom['product_image_cdn'])
            {
                $images[] = 'https://'.$this->cdnHost
                    .$custom['product_image']
                    .'/large.'
                    .$custom['product_image_ext'];

                continue;
            }

            $images[] = 'https://'.$this->host
                .$custom['product_image']
                .'/image.'
                .$custom['product_image_ext'];
        }


        /** Добавляем в массив изображения продукции из карточки */

        foreach($result as $image)
        {
            if($image['product_image_cdn'])
            {
                $images[] = 'https://'.$this->cdnHost
                    .$image['product_image']
                    .'/large.'
                    .$image['product_image_ext'];

                continue;
            }

            $images[] = 'https://'.$this->host
                .$image['product_image']
                .'/image.'
                .$image['product_image_ext'];
        }

        $this->WildberriesProductMediaCardRequest
            ->forTokenIdentifier($message->getIdentifier())
            ->nomenclature($WildberriesCardDTO->getId())
            ->update($images);
    }
}
