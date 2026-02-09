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

namespace BaksDev\Wildberries\Products\Messenger\Cards\CardCreate;

use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByConstInterface;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesProductCreateCardRequest;
use BaksDev\Wildberries\Products\Mapper\WildberriesMapper;
use BaksDev\Wildberries\Products\Messenger\Cards\CardMedia\WildberriesCardMediaUpdateMessage;
use BaksDev\Wildberries\Products\Messenger\Cards\CardPrice\UpdateWildberriesCardPriceMessage;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use BaksDev\Wildberries\Repository\AllProfileToken\AllProfileWildberriesTokenInterface;
use BaksDev\Wildberries\Repository\AllWbTokensByProfile\AllWbTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class WildberriesCardCreateDispatcher
{
    public function __construct(
        #[Target('wildberriesProductsLogger')] private LoggerInterface $logger,
        private WildberriesProductsCardInterface $WildberriesProductsCardRepository,
        private WildberriesProductCreateCardRequest $WildberriesProductCreateCardRequest,
        private CurrentProductIdentifierByConstInterface $CurrentProductIdentifierByConstRepository,
        private WildberriesMapper $wildberriesMapper,
        private MessageDispatchInterface $messageDispatch
    ) {}

    public function __invoke(WildberriesCardCreateMessage $message): void
    {
        $isCardCreate = $this->WildberriesProductCreateCardRequest
            ->forTokenIdentifier($message->getIdentifier())
            ->isCard();

        if(false === $isCardCreate)
        {
            return;
        }

        /**
         * Получаем активные идентификаторы карточки
         */

        $CurrentProductIdentifierResult = $this->CurrentProductIdentifierByConstRepository
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->find();

        if(false === ($CurrentProductIdentifierResult->getBarcode() instanceof ProductBarcode))
        {
            $this->logger->critical('wildberries-products: Ошибка при получении штрихкода при обновлении остатка', [
                self::class.':'.__LINE__, var_export($CurrentProductIdentifierResult, true),
            ]);

            return;
        }


        /**
         * Получаем карточку товара Wildberries для остатка
         */

        $WildberriesProductsCardResult = $this->WildberriesProductsCardRepository
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->forProfile($message->getProfile())
            ->find();

        if(false === ($WildberriesProductsCardResult instanceof WildberriesProductsCardResult))
        {
            $this->logger->warning(
                sprintf('Ошибка: Product Uid: %s. Информация о продукте не была найдена',
                    $message->getProduct()), [self::class.':'.__LINE__],
            );

            return;
        }

        if(empty($WildberriesProductsCardResult->getProductPrice()->getRoundValue()))
        {
            $this->logger->error(
                sprintf('%s: Не добавляем карточку без цены', $WildberriesProductsCardResult->getSearchArticle()),
            );

            return;
        }


        $mapped = $this->wildberriesMapper->getData($WildberriesProductsCardResult);

        if(false === $mapped)
        {
            $this->logger->warning(
                sprintf('Ошибка: Product Uid: %s. Ошибка маппера WB',
                    $message->getProduct()),
            );

            return;
        }

        $requestData = [
            "subjectID" => $mapped['subjectID'],
            "variants" => [$mapped],
        ];


        $isCreate = $this->WildberriesProductCreateCardRequest
            ->forTokenIdentifier($message->getIdentifier())
            ->create($requestData);

        if(false === $isCreate)
        {
            /**
             * Ошибка запишется в лог
             *
             * @see WildberriesProductCreateCardRequest
             */
            return;
        }


        $this->logger->info(sprintf('Создали карточку товара %s', $message->getProduct()));

        /**
         * Обновляем стоимость товара
         */

        $UpdateWildberriesCardPriceMessage = new UpdateWildberriesCardPriceMessage(
            profile: $message->getProfile(),
            identifier: $message->getIdentifier(),
            product: $message->getProduct(),
            offerConst: $message->getOfferConst(),
            variationConst: $message->getVariationConst(),
            modificationConst: $message->getModificationConst(),
            article: $message->getArticle(),
        );

        $this->messageDispatch->dispatch(
            message: $UpdateWildberriesCardPriceMessage,
            stamps: [new MessageDelay('5 seconds')],
            transport: $message->getProfile().'-low',
        );


        /**
         * Обновляем файлы изображений
         */

        $WildberriesCardMediaUpdateMessage = new WildberriesCardMediaUpdateMessage(
            identifier: $message->getIdentifier(),
            product: $message->getProduct(),
            offerConst: $message->getOfferConst(),
            variationConst: $message->getVariationConst(),
            modificationConst: $message->getModificationConst(),
            invariable: $message->getInvariable(),
            article: $message->getArticle(),
        );

        $this->messageDispatch->dispatch(
            message: $WildberriesCardMediaUpdateMessage,
            stamps: [new MessageDelay('30 seconds')],
            transport: $message->getProfile().'-low',
        );

    }
}