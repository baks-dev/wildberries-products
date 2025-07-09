<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Wildberries\Products\Api\Cards\WildberriesProductCreateCardRequest;
use BaksDev\Wildberries\Products\Mapper\WildberriesMapper;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
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
        private WildberriesMapper $wildberriesMapper,
    ) {}

    public function __invoke(WildberriesCardCreateMessage $message): void
    {
        $CurrentWildberriesProductCardResult = $this->WildberriesProductsCardRepository
            ->forProfile($message->getProfile())
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->findResult();

        if(true === empty($CurrentWildberriesProductCardResult))
        {
            $this->logger->warning(
                sprintf('Ошибка: Product Uid: %s. Информация о продукте не была найдена',
                    $message->getProduct())
            );

            return;
        }

        $mapped = $this->wildberriesMapper->getData($CurrentWildberriesProductCardResult);

        if(false === $mapped)
        {
            $this->logger->warning(
                sprintf('Ошибка: Product Uid: %s. Ошибка маппера WB',
                    $message->getProduct())
            );

            return;
        }

        $requestData = [
            "subjectID" => $mapped['subjectID'],
            "variants" => [$mapped],
        ];

        $create = $this->WildberriesProductCreateCardRequest
            ->profile($message->getProfile())
            ->create($requestData);

        if(false === $create)
        {
            /**
             * Ошибка запишется в лог
             *
             * @see WildberriesProductCreateCardRequest
             */
            return;
        }

        /** TODO Сделать добавление цены и остатков */
        $this->logger->info(sprintf('Создали карточку товара %s', $message->getProduct()));
    }
}