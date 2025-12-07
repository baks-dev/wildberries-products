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

namespace BaksDev\Wildberries\Products\Messenger\Cards\CardStocks;


use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Orders\Order\Repository\ProductTotalInOrders\ProductTotalInOrdersInterface;
use BaksDev\Products\Product\Repository\CurrentProductIdentifier\CurrentProductIdentifierByConstInterface;
use BaksDev\Products\Product\Type\Barcode\ProductBarcode;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Wildberries\Products\Api\Stocks\GetWbFbsStocksRequest;
use BaksDev\Wildberries\Products\Api\Stocks\UpdateWbFbsStocksRequest;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use BaksDev\Wildberries\Repository\AllWbTokensByProfile\AllWbTokensByProfileInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Stocks\UpdateYaMarketProductStocksRequest;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 0)]
final readonly class WildberriesProductsStocksDispatcher
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private MessageDispatchInterface $messageDispatch,
        private ProductTotalInOrdersInterface $ProductTotalInOrders,
        private AllWbTokensByProfileInterface $AllWbTokensByProfileRepository,
        private WildberriesProductsCardInterface $WildberriesProductsCardRepository,
        private GetWbFbsStocksRequest $GetWbFbsStocksRequest,
        private CurrentProductIdentifierByConstInterface $CurrentProductIdentifierByConstRepository,
        private UpdateWbFbsStocksRequest $UpdateWbFbsStocksRequest,
    ) {}

    /**
     * Обновляем остатки товаров Wildberries
     */
    public function __invoke(WildberriesProductsStocksMessage $message): void
    {

        /**
         * Получаем все токены профиля
         */

        $tokensByProfile = $this->AllWbTokensByProfileRepository
            ->forProfile($message->getProfile())
            ->findAll();

        if(false === $tokensByProfile || false === $tokensByProfile->valid())
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
            return;
        }

        /**
         * Не обновляем остатки без обязательных параметров
         */
        if(false === $WildberriesProductsCardResult->isCredentials())
        {
            return;
        }

        /**  Остаток товара в карточке либо если подключен модуль складского учета - остаток на складе */
        $ProductQuantity = $WildberriesProductsCardResult->getProductQuantity();

        /** Если подключен модуль складского учета - расчет согласно необработанных заказов */
        if(class_exists(BaksDevProductsStocksBundle::class))
        {
            /** Получаем количество необработанных заказов */
            $unprocessed = $this->ProductTotalInOrders
                ->onProfile($message->getProfile())
                ->onProduct($message->getProduct())
                ->onOfferConst($message->getOfferConst())
                ->onVariationConst($message->getVariationConst())
                ->onModificationConst($message->getModificationConst())
                ->findTotal();

            $ProductQuantity -= $unprocessed;
        }

        $ProductQuantity = max($ProductQuantity, 0);

        foreach($tokensByProfile as $WbTokenUid)
        {
            // -----------------------------------------------------------

            /** Возвращает данные об остатках товаров на маркетплейсе */
            $ProductStocksWildberries = $this->GetWbFbsStocksRequest
                ->forTokenIdentifier($WbTokenUid)
                ->fromBarcode($CurrentProductIdentifierResult->getBarcode())
                ->find();

            if(false === $ProductStocksWildberries)
            {
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('30 seconds')],
                    transport: $message->getProfile().'-low',
                );

                $this->logger->critical(sprintf(
                    'Пробуем обновить остатки штрихкода %s через 30 секунд',
                    $CurrentProductIdentifierResult->getBarcode(),
                ));

                continue;
            }


            /**
             * TRUE - возвращается в случае если продажи остановлены, следовательно, не сверяем остатки, а всегда обнуляем
             *
             * @see UpdateYaMarketProductStocksRequest:79
             */
            if($ProductStocksWildberries !== true && $ProductStocksWildberries === $ProductQuantity)
            {
                $this->logger->info(sprintf(
                    'Наличие соответствует %s: %s == %s',
                    $CurrentProductIdentifierResult->getBarcode(),
                    $ProductStocksWildberries->getTotal(),
                    $ProductQuantity,
                ), [$WbTokenUid]);

                continue;
            }

            /** Обновляем остатки товара если наличие изменилось */
            $this->UpdateWbFbsStocksRequest
                ->forTokenIdentifier($WbTokenUid)
                ->fromBarcode($CurrentProductIdentifierResult->getBarcode())
                ->setTotal($ProductQuantity)
                ->update();

            $this->logger->info(sprintf(
                'Обновили наличие %s: => %s',
                $CurrentProductIdentifierResult->getBarcode(),
                $ProductQuantity,
            ), [$WbTokenUid]);

        }
    }
}
