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

namespace BaksDev\Wildberries\Products\Messenger\WbCardNew;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\ProductByArticle\ProductEventByArticleInterface;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteDTO;
use BaksDev\Products\Product\UseCase\Admin\Delete\ProductDeleteHandler;
use BaksDev\Wildberries\Products\Api\WildberriesCards\Card;
use BaksDev\Wildberries\Products\Api\WildberriesCards\WildberriesCards;
use BaksDev\Wildberries\Products\Repository\Cards\ExistCardOfferByNomenclature\ExistCardOfferByNomenclatureInterface;
use BaksDev\Wildberries\Products\Repository\Settings\ProductSettingsByParentAndName\ProductSettingsByParentAndNameInterface;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\WbProductsSettingsDTO;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class WbCardNewHandler
{
    public function __construct(
        #[Target('wildberriesProductsLogger')] private LoggerInterface $logger,
        private WildberriesCards $wildberriesCards,
        private ProductSettingsByParentAndNameInterface $productSettingsByParentAndName,
        private ExistCardOfferByNomenclatureInterface $existCardOfferByNomenclature,
        private MessageDispatchInterface $messageDispatch,
        private ProductEventByArticleInterface $productEventByArticle,
        private ProductDeleteHandler $productDeleteHandler
    ) {}

    /**
     * Получаем карточки товаров и добавляем отсутствующие
     */
    public function __invoke(WbCardNewMessage $message)
    {
        $profile = $message->getProfile();

        /** Все карточки товаров (Wildberries Api) */
        $WildberriesCards = $this
            ->wildberriesCards
            ->limit(100)
            ->profile($profile);


        $count = 0;

        while(true)
        {
            /** @var Card $Card */
            $cards = $WildberriesCards->findAll();

            foreach($cards as $Card)
            {
                /* Пропускаем, если карточка без фото */
                if($Card->countMedia() === 0)
                {
                    $error = sprintf(
                        '%s: Торговое предложение с номенклатурой %s без фото (%s)',
                        $message->getProfile(),
                        $Card->getNomenclature(),
                        $Card->getArticle(),
                    );

                    $this->logger->warning($error);
                }

                /* Если имеется торговое предложение имеется */
                if($this->existCardOfferByNomenclature->isExist($Card->getNomenclature()))
                {
                    continue;
                }

                /* Если нет номенклатуры, но имеется карточка с артикулом */
                if($ProductEvent = $this->productEventByArticle->findProductEventByArticle($Card->getArticle()))
                {
                    // Удаляем карточку
                    $ProductDeleteDTO = $ProductEvent->getDto(ProductDeleteDTO::class);
                    $this->productDeleteHandler->handle($ProductDeleteDTO);
                }

                $WbProductsSettingsDTO = $this->getSettingsWildberriesRelationCategory($Card->getCategory());

                if(!$WbProductsSettingsDTO)
                {
                    $error = sprintf(
                        '%s: Для товара %s не найдено настройки соотношений %s',
                        $message->getProfile(),
                        $Card->getArticle(),
                        $Card->getCategory(),
                    );

                    $this->logger->warning($error);

                    continue;
                }

                $this->messageDispatch->dispatch(
                    $Card,
                    transport: (string) $profile
                );


            }

            if($WildberriesCards->getCount() < $WildberriesCards->getLimit())
            {

                $error = sprintf(
                    '%s: Карточки успешно обновлены в очередь',
                    $message->getProfile()
                );

                $this->logger->info($error);
                break;
            }
        }
    }


    /** Настройки соотношений категории */
    public function getSettingsWildberriesRelationCategory(string $category): ?WbProductsSettingsDTO
    {
        $WbProductSettingsEvent = $this->productSettingsByParentAndName
            ->get($category);

        return $WbProductSettingsEvent?->getDto(WbProductsSettingsDTO::class);
    }

}