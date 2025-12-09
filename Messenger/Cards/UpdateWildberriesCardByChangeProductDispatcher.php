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

namespace BaksDev\Wildberries\Products\Messenger\Cards;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Messenger\ProductMessage;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\ProductsIdentifierResult;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventResult;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Products\Messenger\Cards\CardCreate\WildberriesCardCreateMessage;
use BaksDev\Wildberries\Products\Messenger\Cards\CardUpdate\WildberriesCardUpdateMessage;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use BaksDev\Wildberries\Repository\AllProfileToken\AllProfileWildberriesTokenInterface;
use BaksDev\Wildberries\Repository\AllWbTokensByProfile\AllWbTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляем карточку Wildberries при изменении системной карточки
 */
#[AsMessageHandler(priority: 10)]
final readonly class UpdateWildberriesCardByChangeProductDispatcher
{
    public function __construct(
        #[Target('wildberriesProductsLogger')] private LoggerInterface $logger,
        private AllProductsIdentifierInterface $AllProductsIdentifierRepository,
        private AllProfileWildberriesTokenInterface $AllProfileWildberriesTokenRepository,
        private MessageDispatchInterface $messageDispatch,
        private FindAllWildberriesCardsRequest $FindAllWildberriesCardsRequest,
        private ProductDetailByEventInterface $ProductDetailByUidRepository,
        private WildberriesProductsCardInterface $WildberriesProductsCardRepository,
        private AllWbTokensByProfileInterface $AllWbTokensByProfileRepository,
    ) {}

    public function __invoke(ProductMessage $message): void
    {
        /**  Получаем все профили пользователей имеющие токены */
        $profiles = $this
            ->AllProfileWildberriesTokenRepository
            ->onlyActiveToken()
            ->findAll();

        if(false === $profiles->valid())
        {
            return;
        }

        /** Получаем идентификаторы обновляемой продукции */
        $products = $this->AllProductsIdentifierRepository
            ->forProduct($message->getId())
            ->toArray();

        if(false === $products)
        {
            return;
        }

        $profiles = iterator_to_array($profiles);

        /** @var ProductsIdentifierResult $ProductsIdentifierResult */

        foreach($products as $ProductsIdentifierResult)
        {
            foreach($profiles as $UserProfileUid)
            {
                $currentProduct = $this->WildberriesProductsCardRepository
                    ->forProduct($ProductsIdentifierResult->getProductId())
                    ->forOfferConst($ProductsIdentifierResult->getProductOfferConst())
                    ->forVariationConst($ProductsIdentifierResult->getProductVariationConst())
                    ->forModificationConst($ProductsIdentifierResult->getProductModificationConst())
                    ->find();

                if(false === ($currentProduct instanceof WildberriesProductsCardResult))
                {
                    $this->logger->warning(
                        sprintf('Ошибка: %s. Информация о продукте не была найдена',
                            $message->getEvent()),
                    );

                    continue;
                }


                /** Если имеется предыдущее событие - карточка не новая, пробуем обновить */
                if(true === ($message->getLast() instanceof ProductEventUid))
                {
                    $lastProduct = $this->ProductDetailByUidRepository
                        ->event($message->getLast())
                        ->offer($ProductsIdentifierResult->getProductOfferId())
                        ->variation($ProductsIdentifierResult->getProductVariationId())
                        ->modification($ProductsIdentifierResult->getProductModificationId())
                        ->findResult();

                    if(false === ($lastProduct instanceof ProductDetailByEventResult))
                    {
                        $this->logger->warning(
                            sprintf('Ошибка: Product Event: %s. Информация о продукте не была найдена',
                                $message->getEvent()),
                        );

                        continue;
                    }

                    $filter = $lastProduct->getProductArticle();

                    if($filter === null)
                    {
                        continue;
                    }
                }

                /**
                 * Получаем все токены профиля пользователя
                 */

                $tokensByProfile = $this->AllWbTokensByProfileRepository
                    ->forProfile($UserProfileUid)
                    ->findAll();

                if(false === $tokensByProfile || false === $tokensByProfile->valid())
                {
                    return;
                }


                foreach($tokensByProfile as $WbTokenUid)
                {
                    $WildberriesCardDTO = $this->FindAllWildberriesCardsRequest
                        ->forTokenIdentifier($WbTokenUid)
                        ->findAll($lastProduct->getProductArticle());

                    /**
                     * Создаем новую карточку товара
                     */
                    if(false === ($WildberriesCardDTO instanceof WildberriesCardDTO))
                    {
                        $wbCreateMessage = new WildberriesCardCreateMessage(
                            profile: $UserProfileUid,
                            product: $ProductsIdentifierResult->getProductId(),
                            offerConst: $ProductsIdentifierResult->getProductOfferConst(),
                            variationConst: $ProductsIdentifierResult->getProductVariationConst(),
                            modificationConst: $ProductsIdentifierResult->getProductModificationConst(),
                            invariable: $ProductsIdentifierResult->getProductInvariable(),
                            article: $lastProduct->getProductArticle(),
                        );

                        /** Транспорт LOW чтобы не мешать общей очереди */
                        $this->messageDispatch->dispatch(
                            message: $wbCreateMessage,
                            transport: $UserProfileUid.'-low',
                        );

                        continue;
                    }

                    /**
                     * Обновляем карточку товара
                     */

                    /** @var WildberriesCardDTO $wbCard */
                    $wbUpdateMessage = new WildberriesCardUpdateMessage(
                        profile: $UserProfileUid,
                        product: $ProductsIdentifierResult->getProductId(),
                        offerConst: $ProductsIdentifierResult->getProductOfferConst(),
                        variationConst: $ProductsIdentifierResult->getProductVariationConst(),
                        modificationConst: $ProductsIdentifierResult->getProductModificationConst(),
                        invariable: $ProductsIdentifierResult->getProductInvariable(),
                        article: $lastProduct->getProductArticle(),
                    );

                    /** Транспорт LOW чтобы не мешать общей очереди */
                    $this->messageDispatch->dispatch(
                        message: $wbUpdateMessage,
                        transport: $UserProfileUid.'-low',
                    );
                }
            }
        }
    }
}