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

namespace BaksDev\Wildberries\Products\Api\GetStocks\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\SellerWarehouse;
use BaksDev\Wildberries\Api\Token\Warehouse\PartnerWildberries\SellerWarehouses;
use BaksDev\Wildberries\Products\Api\GetStocks\FindWildberriesStocksRequest;
use BaksDev\Wildberries\Products\Api\GetStocks\WildberriesStocksDTO;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[When(env: 'test')]
#[Group('wildberries-products')]
final class WarehousesStocksTest extends KernelTestCase
{
    private static $tocken;
    private static $warehouse;
    private static $barcode;

    public static function setUpBeforeClass(): void
    {
        self::$tocken = $_SERVER['TEST_WB_TOCKEN'];
        self::$warehouse = (int) $_SERVER['TEST_WB_WAREHOUSE'];
        self::$barcode = $_SERVER['TEST_WB_BARCODE'];
    }

    public function testUseCase(): void
    {
        /** @var FindWildberriesStocksRequest $WildberriesStocks */
        $WildberriesStocks = self::getContainer()->get(FindWildberriesStocksRequest::class);

        $WildberriesStocks->TokenHttpClient(new WbAuthorizationToken(new UserProfileUid(), self::$tocken));

        /** @var WildberriesStocksDTO $WildberriesStocksDTO */
        $WildberriesStocksDTO = $WildberriesStocks
            ->warehouse(self::$warehouse)
            ->addBarcode(self::$barcode)
            ->stocks();

        if(false === $WildberriesStocksDTO)
        {
            self::assertFalse(false);
            return;
        }

        self::assertNotNull($WildberriesStocksDTO->getAmount(self::$barcode));
        self::assertIsInt($WildberriesStocksDTO->getAmount(self::$barcode));

    }
}