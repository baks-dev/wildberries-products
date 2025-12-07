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

namespace BaksDev\Wildberries\Products\Api\Settings\Category\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Api\Settings\Category\FindAllWbCategoryRequest;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('wildberries-products')]
class WbCategoryTest extends KernelTestCase
{
    private static WbAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new WbAuthorizationToken(
            profile: new UserProfileUid($_SERVER['TEST_WILDBERRIES_PROFILE']),
            token: $_SERVER['TEST_WILDBERRIES_TOKEN'],
            warehouse: $_SERVER['TEST_WILDBERRIES_WAREHOUSE'] ?? null,
            percent: $_SERVER['TEST_WILDBERRIES_PERCENT'] ?? null,
            card: $_SERVER['TEST_WILDBERRIES_CARD'] ?? null,
            stock: $_SERVER['TEST_WILDBERRIES_STOCK'] ?? null,
        );
    }

    public function testUseCase(): void
    {
        /** @var FindAllWbCategoryRequest $FindAllWbCategoryRequest */
        $FindAllWbCategoryRequest = self::getContainer()->get(FindAllWbCategoryRequest::class);
        $FindAllWbCategoryRequest->TokenHttpClient(self::$Authorization);

        $data = $FindAllWbCategoryRequest
            ->parent(1)
            ->findAll();

        foreach($data as $item)
        {
            self::assertIsInt($item->getId());
            self::assertIsString($item->getName());

            //dump($item);

            break;
        }


        self::assertTrue(true);
    }

}