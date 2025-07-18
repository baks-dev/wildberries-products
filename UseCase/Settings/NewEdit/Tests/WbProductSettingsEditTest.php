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

namespace BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Wildberries\Products\Controller\Admin\Settings\Tests\EditControllerTest;
use BaksDev\Wildberries\Products\Entity\Settings\Event\WbProductSettingsEvent;
use BaksDev\Wildberries\Products\Entity\Settings\WbProductSettings;
use BaksDev\Wildberries\Products\Mapper\Property\Collection\BrandWildberriesProductProperty;
use BaksDev\Wildberries\Products\Type\Settings\Event\WbProductSettingsEventUid;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Property\WbProductSettingsPropertyDTO;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\WbProductsSettingsDTO;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\WbProductsSettingsHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries-products
 * @group wildberries-products-settings
 *
 * @depends BaksDev\Wildberries\Products\Controller\Admin\Settings\Tests\EditControllerTest::class
 *
 * @see     EditControllerTest
 */
#[When(env: 'test')]
final class WbProductSettingsEditTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        $WbProductSettingsEvent = $em->getRepository(WbProductSettingsEvent::class)
            ->find(WbProductSettingsEventUid::TEST);

        self::assertNotNull($WbProductSettingsEvent);

        $WbProductsSettingsDTO = new WbProductsSettingsDTO();
        $WbProductSettingsEvent->getDto($WbProductsSettingsDTO);

        self::assertEquals(CategoryProductUid::TEST, (string) $WbProductsSettingsDTO->getMain());

        //self::assertEquals('ffxZnGTCbd', $WbProductsSettingsDTO->getName());
        //$WbProductsSettingsDTO->setName('GCRIVEHZUY');


        /** @var WbProductSettingsPropertyDTO $WbProductSettingsPropertyDTO */

        $WbProductSettingsPropertyDTO = $WbProductsSettingsDTO->getProperty()->current();

        self::assertTrue($WbProductSettingsPropertyDTO->getType()->equals(BrandWildberriesProductProperty::class));

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $WbProductSettingsPropertyDTO->getField());
        $WbProductSettingsPropertyDTO->setField(new CategoryProductSectionFieldUid());


        /** Вспомогательные свойства */

        $WbProductSettingsPropertyDTO->setUnit('rRSzqbqKxA');
        self::assertEquals('rRSzqbqKxA', $WbProductSettingsPropertyDTO->getUnit());

        $WbProductSettingsPropertyDTO->setPopular(false);
        self::assertFalse($WbProductSettingsPropertyDTO->isPopular());

        $WbProductSettingsPropertyDTO->setRequired(false);
        self::assertFalse($WbProductSettingsPropertyDTO->isRequired());


        /** @var WbProductsSettingsHandler $WbProductsSettingsHandler */
        $WbProductsSettingsHandler = self::getContainer()->get(WbProductsSettingsHandler::class);
        $handle = $WbProductsSettingsHandler->handle($WbProductsSettingsDTO);
        self::assertTrue(($handle instanceof WbProductSettings), $handle.': Ошибка WbProductSettings');


    }

}
