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

namespace BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Tests;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Custom\WbBarcodeCustomDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Property\WbBarcodePropertyDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\WbBarcodeDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\WbBarcodeHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('wildberries-products')]
final class EditHandleTest extends KernelTestCase
{
    #[DependsOnClass(NewHandleTest::class)]
    public function testUseCase(): void
    {

        self::bootKernel();
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var WbBarcodeEvent $WbBarcodeEvent */

        $WbBarcodeEvent =
            $em->createQueryBuilder()
                ->select('event')
                ->from(WbBarcode::class, 'barcode')
                ->where('barcode.id = :id')
                ->setParameter('id', CategoryProductUid::TEST, CategoryProductUid::TYPE)
                ->leftJoin(WbBarcodeEvent::class, 'event', 'WITH', 'event.id = barcode.event')
                ->getQuery()
                ->getOneOrNullResult();


        self::assertNotNull($WbBarcodeEvent);


        $WbBarcodeDTO = new WbBarcodeDTO();
        $WbBarcodeEvent->getDto($WbBarcodeDTO);

        self::assertTrue($WbBarcodeDTO->getName()->getValue());
        $WbBarcodeDTO->getName()->setValue(false);

        self::assertTrue($WbBarcodeDTO->getOffer()->getValue());
        $WbBarcodeDTO->getOffer()->setValue(false);

        self::assertTrue($WbBarcodeDTO->getVariation()->getValue());
        $WbBarcodeDTO->getVariation()->setValue(false);

        self::assertTrue($WbBarcodeDTO->getModification()->getValue());
        $WbBarcodeDTO->getModification()->setValue(false);

        self::assertEquals(3, $WbBarcodeDTO->getCounter()->getValue());
        $WbBarcodeDTO->getCounter()->setValue(5);

        /** @var WbBarcodePropertyDTO $WbBarcodePropertyDTO */
        $WbBarcodePropertyDTO = $WbBarcodeDTO->getProperty()->current();

        self::assertEquals(CategoryProductSectionFieldUid::TEST, (string) $WbBarcodePropertyDTO->getOffer());
        self::assertEquals(100, $WbBarcodePropertyDTO->getSort());
        self::assertEquals('Property', $WbBarcodePropertyDTO->getName());


        /** @var WbBarcodeCustomDTO $WbBarcodeCustomDTO */
        $WbBarcodeCustomDTO = $WbBarcodeDTO->getCustom()->current();

        self::assertEquals(100, $WbBarcodeCustomDTO->getSort());
        self::assertEquals('Custom', $WbBarcodeCustomDTO->getName());
        self::assertEquals('Value', $WbBarcodeCustomDTO->getValue());


        /** EDIT */


        // Property
        $WbBarcodePropertyDTO->setOffer(new  CategoryProductSectionFieldUid());
        $WbBarcodePropertyDTO->setSort(50);
        $WbBarcodePropertyDTO->setName('Property Edit');

        // Custom
        $WbBarcodeCustomDTO->setSort(50);
        $WbBarcodeCustomDTO->setName('Custom Edit');
        $WbBarcodeCustomDTO->setValue('Value Edit');


        $UserProfileUid = new UserProfileUid();
        /** @var WbBarcodeHandler $WbBarcodeHandler */
        $WbBarcodeHandler = $container->get(WbBarcodeHandler::class);
        $handle = $WbBarcodeHandler->handle($WbBarcodeDTO, $UserProfileUid);

        self::assertTrue(($handle instanceof WbBarcode), $handle.': Ошибка WbBarcode');
    }


    //    public function testComplete(): void
    //    {
    //
    //        self::bootKernel();
    //        $container = self::getContainer();
    //
    //        /** @var EntityManagerInterface $em */
    //        $em = $container->get(EntityManagerInterface::class);
    //        $WbBarcode = $em->getRepository(WbBarcode::class)
    //            ->findOneBy(['id' => CategoryProductUid::TEST, 'profile' => UserProfileUid::TEST]);
    //        self::assertNotNull($WbBarcode);
    //
    //
    //        self::assertTrue(true);
    //    }
}