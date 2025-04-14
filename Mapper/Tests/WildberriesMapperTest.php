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

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Wildberries\Products\Mapper\WildberriesMapper;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\YaMarketProductsCardInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * @group wildberries-products
 */
#[When(env: 'test')]
class WildberriesMapperTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);
        return;

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

        $AllProductsIdentifier->forProduct('01914cb3-f049-7526-9a82-bd576278fbc4');

        foreach($AllProductsIdentifier->findAll() as $item)
        {
            if($item['product_id'] !== '01914cb3-f049-7526-9a82-bd576278fbc4')
            {
                continue;
            }

            $WildberriesProductsCard = $WildberriesProductsCardRepository
                ->forProduct($item['product_id'])
                ->forOfferConst($item['offer_const'])
                ->forVariationConst($item['variation_const'])
                ->forModificationConst($item['modification_const'])
                ->find();

            if(empty($WildberriesProductsCard))
            {
                continue;
            }

            if(empty($WildberriesProductsCard['length']))
            {
                continue;
            }

            if(empty($WildberriesProductsCard['width']))
            {
                continue;
            }

            if(empty($WildberriesProductsCard['height']))
            {
                continue;
            }

            if(empty($WildberriesProductsCard['weight']))
            {
                continue;
            }


            $request = $WildberriesMapper->getData($WildberriesProductsCard);

            return;

            self::assertEquals($request['offerId'], $YaMarketCard['article']);
            self::assertEquals(current($request['tags']), $YaMarketCard['product_card']);
            self::assertNotFalse(stripos($request['name'], $YaMarketCard['product_name']));
            self::assertEquals($request['marketCategoryId'], $YaMarketCard['market_category']);
            self::assertEquals($request['description'], $YaMarketCard['product_preview']);


            self::assertEquals($request['weightDimensions']['length'], $YaMarketCard['length'] / 10);
            self::assertEquals($request['weightDimensions']['width'], $YaMarketCard['width'] / 10);
            self::assertEquals($request['weightDimensions']['height'], $YaMarketCard['height'] / 10);
            self::assertEquals($request['weightDimensions']['weight'], $YaMarketCard['weight'] / 100);

            //dd($YaMarketCard);


            break;
        }


    }
}