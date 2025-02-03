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

namespace BaksDev\Wildberries\Products\UseCase\Settings\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Wildberries\Products\Controller\Admin\Settings\Tests\DeleteControllerTest;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Settings\Event\WbProductSettingsEvent;
use BaksDev\Wildberries\Products\Entity\Settings\WbProductSettings;
use BaksDev\Wildberries\Products\UseCase\Settings\Delete\DeleteWbProductSettingsDTO;
use BaksDev\Wildberries\Products\UseCase\Settings\Delete\DeleteWbProductSettingsHandler;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsEditTest;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsNewTest;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\WbProductsSettingsDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries-products
 * @group wildberries-products-settings
 *
 * @depends BaksDev\Wildberries\Products\Controller\Admin\Settings\Tests\DeleteControllerTest::class
 * @depends BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Tests\WbProductSettingsEditTest::class
 *
 * @see     WbProductSettingsEditTest
 * @see     DeleteControllerTest
 *
 */
#[When(env: 'test')]
final class WbProductSettingsDeleteTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        $container = self::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);
        $CategoryProductUid = new CategoryProductUid();

        $qb
            ->from(WbProductSettings::class, 'main')
            ->where('main.id = :category')
            ->setParameter('category', $CategoryProductUid, CategoryProductUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(
                WbProductSettingsEvent::class,
                'event',
                'WITH',
                'event.id = main.event'
            );


        /** @var WbBarcodeEvent $WbProductSettingsEvent */
        $WbProductSettingsEvent = $qb->getQuery()->getOneOrNullResult();


        /**  WbBarcodeDTO  */


        $WbProductCardDTO = new WbProductsSettingsDTO();
        $WbProductSettingsEvent->getDto($WbProductCardDTO);

        /**
         * WbProductCardDTO
         */

        self::assertEquals(CategoryProductUid::TEST, (string) $WbProductCardDTO->getMain());
        self::assertEquals('GCRIVEHZUY', $WbProductCardDTO->getName());

        /**
         * WbProductSettingsPropertyDTO
         */

        $WbProductSettingsPropertyDTO = $WbProductCardDTO->getProperty()->current();

        self::assertEquals('fTXTGyZxIr', $WbProductSettingsPropertyDTO->getType());
        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $WbProductSettingsPropertyDTO->getField());


        /** DELETE */

        $DeleteWbProductSettingsDTO = new DeleteWbProductSettingsDTO();
        $WbProductSettingsEvent->getDto($DeleteWbProductSettingsDTO);

        /** @var DeleteWbProductSettingsHandler $DeleteWbProductSettingsHandler */
        $DeleteWbProductSettingsHandler = $container->get(DeleteWbProductSettingsHandler::class);
        $handle = $DeleteWbProductSettingsHandler->handle($DeleteWbProductSettingsDTO);
        self::assertTrue(($handle instanceof WbProductSettings), $handle.': Ошибка WbProductSettings');

    }

    public static function tearDownAfterClass(): void
    {
        WbProductSettingsNewTest::setUpBeforeClass();
    }


}
