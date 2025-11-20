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

namespace BaksDev\Wildberries\Products\Messenger\Cards\CardUpdate;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesProductUpdateCardRequest;
use BaksDev\Wildberries\Products\Mapper\WildberriesMapper;
use BaksDev\Wildberries\Products\Messenger\Cards\CardMedia\WildberriesCardMediaUpdateMessage;
use BaksDev\Wildberries\Products\Messenger\Cards\CardPrice\UpdateWildberriesCardPriceMessage;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class WildberriesCardUpdateDispatcher
{
    public function __construct(
        #[Target('wildberriesProductsLogger')] private LoggerInterface $logger,
        private WildberriesProductsCardInterface $WildberriesProductsCardRepository,
        private WildberriesProductUpdateCardRequest $WildberriesProductUpdateCardRequest,
        private FindAllWildberriesCardsRequest $FindAllWildberriesCardsRequest,
        private WildberriesMapper $wildberriesMapper,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    public function __invoke(WildberriesCardUpdateMessage $message): void
    {
        $CurrentWildberriesProductCardResult = $this->WildberriesProductsCardRepository
            ->forProfile($message->getProfile())
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->findResult();

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


        $requestData = $this->wildberriesMapper->getData($CurrentWildberriesProductCardResult);

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
        $requestData['nmId'] = $WildberriesCardDTO->getId();

        /** Удаляем имеющиеся штрихкоды */
        foreach($requestData['sizes'] as $i => $size)
        {
            /** Получаем идентификаторы chrt для штрихкодов */

            foreach($WildberriesCardDTO->getOffersCollection() as $barcode => $number)
            {
                $key = array_search($barcode, $size['skus'], true);

                if($key !== false)
                {
                    /** Удаляем штрихкод и задаем идентификатор свойства */
                    unset($requestData['sizes'][$i]['skus'][$key]);
                    $requestData['sizes'][$i]['chrtID'] = $WildberriesCardDTO->getChrt($number);
                }
            }
        }

        $update = $this->WildberriesProductUpdateCardRequest
            ->profile($message->getProfile())
            ->update($requestData);

        if(false === $update)
        {
            /**
             * Ошибка запишется в лог
             *
             * @see WildberriesProductUpdateCardRequest
             */

            return;
        }

        $this->logger->info(sprintf('Обновили карточку товара %s', $message->getProduct()));


        /**
         * Обновляем стоимость товара
         */

        $UpdateWildberriesCardPriceMessage = new UpdateWildberriesCardPriceMessage
        (
            profile: $message->getProfile(),
            product: $message->getProduct(),
            offerConst: $message->getOfferConst(),
            variationConst: $message->getVariationConst(),
            modificationConst: $message->getModificationConst(),
            article: $message->getArticle(),
        );

        $this->messageDispatch->dispatch(
            message: $UpdateWildberriesCardPriceMessage,
            stamps: [new MessageDelay('5 seconds')],
            transport: (string) $message->getProfile(),
        );


        /**
         * Обновляем файлы изображений
         */

        $WildberriesCardMediaUpdateMessage = new WildberriesCardMediaUpdateMessage(
            profile: $message->getProfile(),
            product: $message->getProduct(),
            offerConst: $message->getOfferConst(),
            variationConst: $message->getVariationConst(),
            modificationConst: $message->getModificationConst(),
            article: $message->getArticle(),
        );

        $this->messageDispatch->dispatch(
            message: $WildberriesCardMediaUpdateMessage,
            stamps: [new MessageDelay('10 seconds')],
            transport: $message->getProfile().'-low',
        );
    }
}
