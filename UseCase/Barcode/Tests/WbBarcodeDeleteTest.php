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

namespace BaksDev\Wildberries\Products\UseCase\Barcode\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;
use BaksDev\Wildberries\Products\UseCase\Barcode\Delete\WbBarcodeDeleteDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\Delete\WbBarcodeDeleteHandler;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Custom\WbBarcodeCustomDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Property\WbBarcodePropertyDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\WbBarcodeDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group wildberries-products */
#[When(env: 'test')]
final class WbBarcodeDeleteTest extends KernelTestCase
{
    /**
     * @depends BaksDev\Wildberries\Products\UseCase\Barcode\Tests\WbBarcodeEditTest::testUseCase
     */
    public function testUseCase(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);
        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);
        $CategoryProductUid = new CategoryProductUid(CategoryProductUid::TEST);

        $qb
            ->from(WbBarcode::class, 'barcode')
            ->where('barcode.id = :category')
            ->setParameter('category', $CategoryProductUid, CategoryProductUid::TYPE);

        $qb
            ->select('event')
            ->leftJoin(WbBarcodeEvent::class,
                'event',
                'WITH',
                'event.id = barcode.event'
            );


        /** @var WbBarcodeEvent $WbBarcodeEvent */
        $WbBarcodeEvent = $qb->getQuery()->getOneOrNullResult();

        $WbBarcodeDTO = new WbBarcodeDTO();
        $WbBarcodeEvent->getDto($WbBarcodeDTO);


        self::assertFalse($WbBarcodeDTO->getOffer());
        self::assertFalse($WbBarcodeDTO->getVariation());
        self::assertEquals(5, $WbBarcodeDTO->getCounter());


        /** @var WbBarcodePropertyDTO $WbBarcodePropertyDTO */
        $WbBarcodePropertyDTO = $WbBarcodeDTO->getProperty()->current();

        self::assertNotEquals(CategoryProductSectionFieldUid::TEST, (string) $WbBarcodePropertyDTO->getOffer());
        self::assertEquals(50, $WbBarcodePropertyDTO->getSort());
        self::assertEquals('Property Edit', $WbBarcodePropertyDTO->getName());


        /** @var WbBarcodeCustomDTO $WbBarcodeCustomDTO */
        $WbBarcodeCustomDTO = $WbBarcodeDTO->getCustom()->current();

        self::assertEquals(50, $WbBarcodeCustomDTO->getSort());
        self::assertEquals('Custom Edit', $WbBarcodeCustomDTO->getName());
        self::assertEquals('Value Edit', $WbBarcodeCustomDTO->getValue());


        /** DELETE */


        $WbBarcodeDeleteDTO = new WbBarcodeDeleteDTO();
        $WbBarcodeEvent->getDto($WbBarcodeDeleteDTO);

        /** @var WbBarcodeDeleteHandler $WbBarcodeDeleteHandler */
        $UserProfileUid = new UserProfileUid(UserProfileUid::TEST);
        $WbBarcodeDeleteHandler = $container->get(WbBarcodeDeleteHandler::class);
        $handle = $WbBarcodeDeleteHandler->handle($WbBarcodeDeleteDTO, $UserProfileUid);
        self::assertTrue(($handle instanceof WbBarcode));

        /**
         * Очищаем все события
         * @var EntityManagerInterface $em
         */
        $em = $container->get(EntityManagerInterface::class);
        $WbBarcodeEventCollection = $em->getRepository(WbBarcodeEvent::class)
            ->findBy(['category' => $CategoryProductUid]);

        foreach($WbBarcodeEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();
    }
}
