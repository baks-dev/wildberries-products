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

namespace BaksDev\Wildberries\Products\Command;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\ProductsIdentifierResult;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventInterface;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByEventResult;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Products\Messenger\Cards\CardCreate\WildberriesCardCreateMessage;
use BaksDev\Wildberries\Products\Messenger\Cards\CardUpdate\WildberriesCardUpdateMessage;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use BaksDev\Wildberries\Repository\AllProfileToken\AllProfileWildberriesTokenInterface;
use BaksDev\Wildberries\Repository\AllWbTokensByProfile\AllWbTokensByProfileInterface;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use DateInterval;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Обновляем карточки товаров на WB
 */
#[AsCommand(
    name: 'baks:wildberries-products:update:cards',
    description: 'Обновляет все карточки на WB',
    aliases: ['baks:wildberries:update:cards', 'baks:wb:update:cards']
)]
final class UpdateWildberriesProductsCardsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileWildberriesTokenInterface $AllProfileToken,
        private readonly AllProductsIdentifierInterface $AllProductsIdentifier,
        private readonly ProductDetailByEventInterface $ProductDetailByEventRepository,
        private readonly MessageDispatchInterface $messageDispatch,
        private readonly FindAllWildberriesCardsRequest $FindAllWildberriesCardsRequest,
        private readonly AllWbTokensByProfileInterface $AllWbTokensByProfileRepository,
        private readonly AppCacheInterface $appCache
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('article', 'a', InputOption::VALUE_OPTIONAL, 'Фильтр по артикулу ((--article=... || -a ...))');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /** Получаем активные токены авторизации профилей */
        $profiles = $this->AllProfileToken
            ->onlyActiveToken()
            ->findAll();

        $profiles = iterator_to_array($profiles);

        $helper = $this->getHelper('question');


        /**
         * Интерактивная форма списка профилей
         */

        $questions[] = 'Все';

        foreach($profiles as $quest)
        {
            $questions[] = $quest->getAttr();
        }

        $questions['+'] = 'Выполнить все асинхронно';
        $questions['-'] = 'Выйти';

        $question = new ChoiceQuestion(
            'Профиль пользователя (Ctrl+C чтобы выйти)',
            $questions,
            '0',
        );

        $key = $helper->ask($input, $output, $question);

        /**
         *  Выходим без выполненного запроса
         */

        if($key === '-' || $key === 'Выйти')
        {
            return Command::SUCCESS;
        }


        /**
         * Выполняем все с возможностью асинхронно в очереди
         */

        if($key === '+' || $key === '0' || $key === 'Все')
        {
            /** @var UserProfileUid $profile */
            foreach($profiles as $profile)
            {
                $this->update($profile, $input->getOption('article'), $key === '+');
            }

            $this->io->success('Обновление успешно запущено');
            return Command::SUCCESS;
        }


        /**
         * Выполняем определенный профиль
         */

        $UserProfileUid = null;

        foreach($profiles as $profile)
        {
            if($profile->getAttr() === $questions[$key])
            {
                /* Присваиваем профиль пользователя */
                $UserProfileUid = $profile;
                break;
            }
        }

        if($UserProfileUid)
        {
            $this->update($UserProfileUid, $input->getOption('article'));

            $this->io->success('Карточки успешно обновлены');
            return Command::SUCCESS;
        }


        $this->io->success('Профиль пользователя не найден');
        return Command::SUCCESS;

    }

    public function update(UserProfileUid $UserProfileUid, ?string $article = null, bool $async = false): void
    {
        $this->io->note(sprintf('Обновляем профиль %s', $UserProfileUid->getAttr()));

        /*
         * Получаем все имеющиеся карточки в системе.
         * Для тестирования используем вызов метода ->forProduct('0197a6d6-c8e6-7df0-b979-a44f2800eca8') перед findALl
         * */
        $products = $this->AllProductsIdentifier
            ->forProfile($UserProfileUid)
            ->findAll();

        if(false === $products || false === $products->valid())
        {
            $this->io->warning('Карточек для обновления не найдено');
            return;
        }

        /**
         * Получаем все токены профиля
         */

        $tokensByProfile = $this->AllWbTokensByProfileRepository
            ->forProfile($UserProfileUid)
            ->findAll();

        if(false === $tokensByProfile || false === $tokensByProfile->valid())
        {
            return;
        }

        $tokens = iterator_to_array($tokensByProfile);

        /** @var ProductsIdentifierResult $ProductsIdentifierResult */
        foreach($products as $ProductsIdentifierResult)
        {
            if(!empty($article) && stripos($ProductsIdentifierResult->getArticle(), $article) === false)
            {
                $this->io->writeln(sprintf('<fg=gray>... %s</>', $ProductsIdentifierResult->getArticle()));
                continue;
            }

            $ProductDetailByEventResult = $this->ProductDetailByEventRepository
                ->event($ProductsIdentifierResult->getProductEvent())
                ->offer($ProductsIdentifierResult->getProductOfferId())
                ->variation($ProductsIdentifierResult->getProductVariationId())
                ->modification($ProductsIdentifierResult->getProductModificationId())
                ->findResult();

            if(false === ($ProductDetailByEventResult instanceof ProductDetailByEventResult))
            {
                $this->io->warning('Карточки не найдено, либо не указаны настройки соотношений свойств');

                continue;
            }

            /**
             * Если передан артикул - применяем фильтр по вхождению
             * Пропускаем обновление, если соответствие не найдено
             */

            if(!empty($article) && stripos($ProductDetailByEventResult->getProductArticle(), $article) === false)
            {
                continue;
            }


            /** @var WbTokenUid $WbTokenUid */
            foreach($tokens as $WbTokenUid)
            {
                $isCardUpdate = $this->FindAllWildberriesCardsRequest
                    ->forTokenIdentifier($WbTokenUid)
                    ->isCard();

                if(false === $isCardUpdate)
                {
                    continue;
                }

                $wbCard = $this->FindAllWildberriesCardsRequest
                    ->forTokenIdentifier($WbTokenUid)
                    ->allPhoto()
                    ->findAll($ProductDetailByEventResult->getProductArticle());

                /** Если карточка на WB не существует и ее нужно создать */
                if(false === $wbCard || false === $wbCard->valid())
                {
                    $wildberriesProductCardCreateMessage = new WildberriesCardCreateMessage(
                        identifier: $WbTokenUid,
                        profile: $UserProfileUid,
                        product: $ProductsIdentifierResult->getProductId(),
                        offerConst: $ProductsIdentifierResult->getProductOfferConst(),
                        variationConst: $ProductsIdentifierResult->getProductVariationConst(),
                        modificationConst: $ProductsIdentifierResult->getProductModificationConst(),
                        invariable: $ProductsIdentifierResult->getProductInvariable(),
                        article: $ProductDetailByEventResult->getProductArticle(),
                    );

                    /** Консольную комманду выполняем синхронно */
                    $this->messageDispatch->dispatch(
                        message: $wildberriesProductCardCreateMessage,
                        transport: $async === true ? $UserProfileUid.'-low' : null,
                    );

                    $this->io->text(sprintf('Создали новую карточку WB для артикула %s', $ProductDetailByEventResult->getProductArticle()));

                    if($ProductDetailByEventResult->getProductArticle() === $article)
                    {
                        break;
                    }

                    continue;
                }


                /**
                 * В случае, если на WB нужная карточка уже существует
                 *
                 * @var WildberriesCardDTO $WildberriesCardDTO
                 */
                $WildberriesCardDTO = $wbCard->current();


                /** Сохраняем идентификатор группировки карточек */


                /** Шины группируем по РадиусПрофильШирина */
                if($WildberriesCardDTO->getCategory() === WildberriesProductProperty::CATEGORY_TIRE)
                {
                    $key = $ProductDetailByEventResult->getProductOfferValue()
                        .$ProductDetailByEventResult->getProductVariationValue()
                        .$ProductDetailByEventResult->getProductModificationValue();
                }
                else
                {
                    $key = $ProductDetailByEventResult->getProductCardArticle();
                }

                $cache = $this->appCache->init('wildberries-products');

                $cache->get($key, function(ItemInterface $item) use ($WildberriesCardDTO): int {

                    /** По умолчанию кешируем на 1 сек на случай, если результат вернет FALSE */
                    $item->expiresAfter(DateInterval::createFromDateString('1 day'));

                    return $WildberriesCardDTO->getNomenclature();
                });


                $wildberriesProductCardUpdateMessage = new WildberriesCardUpdateMessage(
                    identifier: $WbTokenUid,
                    profile: $UserProfileUid,
                    product: $ProductsIdentifierResult->getProductId(),
                    offerConst: $ProductsIdentifierResult->getProductOfferConst(),
                    variationConst: $ProductsIdentifierResult->getProductVariationConst(),
                    modificationConst: $ProductsIdentifierResult->getProductModificationConst(),
                    invariable: $ProductsIdentifierResult->getProductInvariable(),
                    article: $ProductDetailByEventResult->getProductArticle(),
                );

                /** Консольную комманду выполняем синхронно */
                $this->messageDispatch->dispatch(
                    message: $wildberriesProductCardUpdateMessage,
                    transport: $async === true ? $UserProfileUid.'-low' : null,
                );


            }


            $this->io->text(sprintf('Обновили артикул %s', $ProductDetailByEventResult->getProductArticle()));

            if($ProductDetailByEventResult->getProductArticle() === $article)
            {
                break;
            }
        }
    }
}