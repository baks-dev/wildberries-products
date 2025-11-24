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

namespace BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\Tests;

use BaksDev\Core\BaksDevCoreBundle;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Wildberries\Products\Entity\Custom\WildberriesProductCustom;
use BaksDev\Wildberries\Products\Type\Custom\Image\WbProductCustomImageUid;
use BaksDev\Wildberries\Products\Type\Id\WildberriesProductUid;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\Images\WildberriesProductCustomImagesDTO;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\WildberriesCustomProductDTO;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\WildberriesCustomProductHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

#[When(env: 'test')]
#[Group('wildberries-products')]
final class WildberriesProductEditTest extends KernelTestCase
{
    #[DependsOnClass(WildberriesProductNewTest::class)]
    public function testEdit(): void
    {
        /** @var ContainerBagInterface $containerBag */
        $container = self::getContainer();
        $containerBag = $container->get(ContainerBagInterface::class);
        $fileSystem = $container->get(Filesystem::class);

        /** Создаем путь к тестовой директории */
        $testUploadDir = implode(
            DIRECTORY_SEPARATOR,
            [$containerBag->get('kernel.project_dir'), 'public', 'upload', 'tests'],
        );

        $fileSystem->copy(
            BaksDevCoreBundle::PATH.implode(
                DIRECTORY_SEPARATOR,
                ['Resources', 'assets', 'img', 'empty.webp'],
            ),
            $testUploadDir.DIRECTORY_SEPARATOR.'photo1.webp',
        );

        $filePhoto = new File($testUploadDir.DIRECTORY_SEPARATOR.'photo.webp', false);

        $em = $container->get(EntityManagerInterface::class);

        /** @var WildberriesProductCustom $product */
        $product = $em
            ->getRepository(WildberriesProductCustom::class)
            ->find(WbProductCustomImageUid::TEST);

        self::assertNotNull($product);

        $editDTO = new WildberriesCustomProductDTO();
        $imageDTO = new WildberriesProductCustomImagesDTO();
        $imageDTO->setFile($filePhoto);

        $editDTO->getImages()->add($imageDTO);

        $product->getDto($editDTO);

        self::assertTrue($editDTO->getInvariable()->equals(ProductInvariableUid::TEST));

        self::assertNotEmpty($editDTO->getImages());

        /** @var WildberriesCustomProductHandler $handler */
        $handler = $container->get(WildberriesCustomProductHandler::class);
        $editWildberriesProduct = $handler->handle($editDTO);
        self::assertTrue($editWildberriesProduct instanceof WildberriesProductCustom);
        self::assertTrue($editWildberriesProduct->getImages()->current()->getName() === 'photo1');
    }
}