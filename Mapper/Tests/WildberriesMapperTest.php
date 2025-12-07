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

namespace BaksDev\Wildberries\Products\Mapper\Tests;

use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Mapper\WildberriesMapper;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[When(env: 'test')]
#[Group('wildberries-products')]
class WildberriesMapperTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);

        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var AllProductsIdentifierInterface $AllProductsIdentifier */
        $AllProductsIdentifier = self::getContainer()->get(AllProductsIdentifierInterface::class);

        /** @var WildberriesProductsCardInterface $WildberriesProductsCardRepository */
        $WildberriesProductsCardRepository = self::getContainer()->get(WildberriesProductsCardInterface::class);

        /** @var WildberriesMapper $WildberriesMapper */
        $WildberriesMapper = self::getContainer()->get(WildberriesMapper::class);

        foreach($AllProductsIdentifier->findAll() as $ProductsIdentifierResult)
        {
            $WildberriesProductsCard = $WildberriesProductsCardRepository
                ->forProfile(UserProfileUid::TEST)
                ->forProduct($ProductsIdentifierResult->getProductId())
                ->forOfferConst($ProductsIdentifierResult->getProductOfferConst())
                ->forVariationConst($ProductsIdentifierResult->getProductVariationConst())
                ->forModificationConst($ProductsIdentifierResult->getProductModificationConst())
                ->find();

            if(empty($WildberriesProductsCard))
            {
                continue;
            }

            if(empty($WildberriesProductsCard->getLength()))
            {
                continue;
            }

            if(empty($WildberriesProductsCard->getWidth()))
            {
                continue;
            }

            if(empty($WildberriesProductsCard->getHeight()))
            {
                continue;
            }

            if(empty($WildberriesProductsCard->getWeight()))
            {
                continue;
            }


            $request = $WildberriesMapper->getData($WildberriesProductsCard);

            self::assertEquals($request['offerId'], $WildberriesProductsCard->getArticle());
            self::assertEquals(current($request['tags']), $WildberriesProductsCard->getProductCard());
            self::assertNotFalse(stripos($request['name'], $WildberriesProductsCard->getProductName()));
            self::assertEquals($request['marketCategoryId'], $WildberriesProductsCard->getMarketCategory());
            self::assertEquals($request['description'], $WildberriesProductsCard->getProductPreview());


            self::assertEquals($request['weightDimensions']['length'], $WildberriesProductsCard->getLength() / 10);
            self::assertEquals($request['weightDimensions']['width'], $WildberriesProductsCard->getWidth() / 10);
            self::assertEquals($request['weightDimensions']['height'], $WildberriesProductsCard->getHeight() / 10);
            self::assertEquals($request['weightDimensions']['weight'], $WildberriesProductsCard->getWeight() / 100);

            break;
        }


    }
}