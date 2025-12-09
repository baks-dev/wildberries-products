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


use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Entity\Event\OrderEvent;
use BaksDev\Orders\Order\Messenger\OrderMessage;
use BaksDev\Orders\Order\Repository\CurrentOrderEvent\CurrentOrderEventInterface;
use BaksDev\Orders\Order\UseCase\Admin\Edit\EditOrderDTO;
use BaksDev\Orders\Order\UseCase\Admin\Edit\Products\OrderProductDTO;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByEventInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierResult;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Messenger\Cards\CardStocks\WildberriesProductsStocksMessage;
use BaksDev\Wildberries\Repository\AllProfileToken\AllProfileWildberriesTokenInterface;
use BaksDev\Wildberries\Repository\AllWbTokensByProfile\AllWbTokensByProfileInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class UpdateStocksWildberriesWhenChangeOrderStatusDispatcher
{
    public function __construct(
        private AllProfileWildberriesTokenInterface $AllProfileWildberriesTokenRepository,
        private AllWbTokensByProfileInterface $AllWbTokensByProfileRepository,
        private CurrentProductIdentifierByEventInterface $currentProductIdentifier,
        private CurrentOrderEventInterface $CurrentOrderEvent,
        private DeduplicatorInterface $deduplicator,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    public function __invoke(OrderMessage $message): void
    {
        /** Получаем событие заказа */
        $OrderEvent = $this->CurrentOrderEvent
            ->forOrder($message->getId())
            ->find();

        if(false === ($OrderEvent instanceof OrderEvent))
        {
            return;
        }


        /** Если имеется информация о профиле заказа - обновляем только указанный профиль  */
        if($OrderEvent->getOrderProfile() instanceof UserProfileUid)
        {
            $profiles = [$OrderEvent->getOrderProfile()];
        }
        else
        {
            /**  Получаем активные все активные профили у которых имеется токен Wildberries */
            $profiles = $this->AllProfileWildberriesTokenRepository
                ->onlyActiveToken()
                ->findAll();

            if(false === $profiles || false === $profiles->valid())
            {
                return;
            }
        }


        /** Дедубликатор изменения статусов (обновляем только один раз в сутки на статус) */

        $Deduplicator = $this->deduplicator
            ->namespace('wildberries-products')
            ->expiresAfter('1 day')
            ->deduplication([
                (string) $message->getId(),
                $OrderEvent->getStatus()->getOrderStatusValue(),
                self::class,
            ]);

        if($Deduplicator->isExecuted())
        {
            return;
        }

        $Deduplicator->save();

        /**
         * Обновляем остатки
         */

        $EditOrderDTO = new EditOrderDTO();
        $OrderEvent->getDto($EditOrderDTO);

        foreach($profiles as $UserProfileUid)
        {
            /** Получаем все токены авторизации профиля */

            $tokens = $this->AllWbTokensByProfileRepository
                ->forProfile($UserProfileUid)
                ->findAll();

            if(false === $tokens || false === $tokens->valid())
            {
                continue;
            }

            foreach($tokens as $WbTokenUid)
            {
                /** @var OrderProductDTO $product */
                foreach($EditOrderDTO->getProduct() as $product)
                {
                    /** Получаем идентификаторы обновляемой продукции для получения констант  */
                    $CurrentProductIdentifier = $this->currentProductIdentifier
                        ->forEvent($product->getProduct())
                        ->forOffer($product->getOffer())
                        ->forVariation($product->getVariation())
                        ->forModification($product->getModification())
                        ->find();

                    if(false === ($CurrentProductIdentifier instanceof CurrentProductIdentifierResult))
                    {
                        continue;
                    }

                    $WildberriesProductsStocksMessage = new WildberriesProductsStocksMessage(
                        profile: $UserProfileUid,
                        identifier: $WbTokenUid,
                        product: $CurrentProductIdentifier->getProduct(),
                        offerConst: $CurrentProductIdentifier->getOfferConst(),
                        variationConst: $CurrentProductIdentifier->getVariationConst(),
                        modificationConst: $CurrentProductIdentifier->getModificationConst(),
                    );

                    /** Добавляем в очередь обновление остатков через транспорт профиля */

                    $this->messageDispatch->dispatch(
                        message: $WildberriesProductsStocksMessage,
                        stamps: [new MessageDelay('5 seconds')],
                        transport: (string) $UserProfileUid,
                    );
                }
            }
        }
    }
}
