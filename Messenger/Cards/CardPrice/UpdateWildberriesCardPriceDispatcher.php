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

namespace BaksDev\Wildberries\Products\Messenger\Cards\CardPrice;


use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\UpdateWildberriesProductPriceRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Products\Mapper\WildberriesMapper;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Метод обновляет цены на товар
 */
#[AsMessageHandler(priority: 0)]
final class UpdateWildberriesCardPriceDispatcher
{
    public function __construct(
        #[Target('wildberriesProductsLogger')] private LoggerInterface $logger,
        private WildberriesProductsCardInterface $WildberriesProductsCardRepository,
        private UpdateWildberriesProductPriceRequest $UpdateWildberriesProductPriceRequest,
        private FindAllWildberriesCardsRequest $FindAllWildberriesCardsRequest,
        private WildberriesMapper $wildberriesMapper,
    ) {}

    public function __invoke(UpdateWildberriesCardPriceMessage $message): void
    {
        $CurrentWildberriesProductCardResult = $this->WildberriesProductsCardRepository
            ->forProfile($message->getProfile())
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if(false === ($CurrentWildberriesProductCardResult instanceof WildberriesProductsCardResult))
        {
            $this->logger->warning(
                sprintf('%s: Информация о продукте не была найдена',
                    $message->getArticle()),
                [self::class.':'.__LINE__, var_export($message, true)],
            );

            return;
        }

        /** Получаем текущее состояние карточки Wildberries */

        $wbCard = $this->FindAllWildberriesCardsRequest
            ->profile($message->getProfile())
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

        $requestData = $this->wildberriesMapper
            ->getData($CurrentWildberriesProductCardResult);

        if(false === $requestData)
        {
            $this->logger->warning(
                sprintf('Ошибка: Product Uid: %s. Ошибка маппера WB',
                    $message->getProduct()),
            );

            return;
        }

        /** @var WildberriesCardDTO $WildberriesCardDTO */
        $WildberriesCardDTO = $wbCard->current();

        $this->UpdateWildberriesProductPriceRequest
            ->profile($message->getProfile())
            ->nomenclature($WildberriesCardDTO->getId());


        /**
         * Для товаров, позволяющих указывать цены на размеры - находим идентификатор размера
         * реализовать метод
         *
         * @see https://dev.wildberries.ru/openapi/work-with-products#tag/Ceny-i-skidki/paths/~1api~1v2~1upload~1task~1size/post
         */

        //        foreach($requestData['sizes'] as $i => $size)
        //        {
        //            /** Получаем идентификаторы chrt для штрихкодов */
        //            foreach($WildberriesCardDTO->getOffersCollection() as $barcode => $number)
        //            {
        //                $key = array_search($barcode, $size['skus'], true);
        //
        //                if($key !== false)
        //                {
        //                    /** Задаем идентификатор размера */
        //                    $requestData['sizes'][$i]['chrtID'] = $WildberriesCardDTO->getChrt($number);
        //                }
        //            }
        //        }

        foreach($requestData['sizes'] as $size)
        {
            $isUpdate = $this->UpdateWildberriesProductPriceRequest
                ->price($size['price'])
                ->update();

            if(false === $isUpdate)
            {
                $this->logger->warning(
                    sprintf('%s: Пробуем обновить стоимость товара позже', $message->getArticle()),
                );

                continue;
            }

            $this->logger->info(
                sprintf('%s: Обновили стоимость артикула', $message->getArticle()),
            );
        }
    }
}
