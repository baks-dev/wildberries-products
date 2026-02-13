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

namespace BaksDev\Wildberries\Products\Repository\Custom\WildberriesProductImageIdentifierByName\Tests;

use BaksDev\Wildberries\Products\Repository\Custom\WildberriesProductImageIdentifierByName\WildberriesProductImageIdentifierByNameInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('wildberries-products')]
#[Group('wildberries-products-repository')]
class WildberriesProductImageIdentifierByNameRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var WildberriesProductImageIdentifierByNameInterface $WildberriesImageIdentifierByNameRepository */
        $WildberriesImageIdentifierByNameRepository = self::getContainer()->get(WildberriesProductImageIdentifierByNameInterface::class);

        $result = $WildberriesImageIdentifierByNameRepository->find('88f9fbfeb61b28a041aff4c9c8add3ee');

//        dump($result);

        self::assertTrue(true);

    }
}