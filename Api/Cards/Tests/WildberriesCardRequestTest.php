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

namespace BaksDev\Wildberries\Products\Api\Cards\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Api\Cards\FindAllWildberriesCardsRequest;
use BaksDev\Wildberries\Products\Api\Cards\WildberriesCardDTO;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries-products
 */
#[When(env: 'test')]
class WildberriesCardRequestTest extends KernelTestCase
{
    private static WbAuthorizationToken $Authorization;

    public static function setUpBeforeClass(): void
    {
        self::$Authorization = new WbAuthorizationToken(
            new UserProfileUid($_SERVER['TEST_WILDBERRIES_PROFILE']),
            $_SERVER['TEST_WILDBERRIES_TOKEN']
        );
    }

    public function testUseCase(): void
    {
        /** @var FindAllWildberriesCardsRequest $WildberriesGetCardsRequest */
        $WildberriesGetCardsRequest = self::getContainer()->get(FindAllWildberriesCardsRequest::class);
        $WildberriesGetCardsRequest->TokenHttpClient(self::$Authorization);

        $data = $WildberriesGetCardsRequest->findAll('78346396');
        /** @var WildberriesCardDTO $Card */
        foreach($data as $Card)
        {
            self::assertInstanceOf(WildberriesCardDTO::class, $Card);

            self::assertIsInt($Card->getId());
            self::assertIsInt($Card->getNomenclature());

            self::assertNotEmpty($Card->getCategory());
            self::assertIsInt($Card->getCategory());

            self::assertNotEmpty($Card->getName());
            self::assertIsString($Card->getName());

            self::assertNotEmpty($Card->getArticle());
            self::assertIsString($Card->getArticle());

            self::assertTrue($Card->getMedia()->count() > 0);
            self::assertTrue($Card->getCharacteristicsCollection()->count() > 0);

            self::assertTrue($Card->getOffersCollection()->count() > 0);

            self::assertTrue($Card->getChrt('XL') === false || is_int($Card->getChrt('XL')));

            break;
        }


        self::assertTrue(true);
    }

}