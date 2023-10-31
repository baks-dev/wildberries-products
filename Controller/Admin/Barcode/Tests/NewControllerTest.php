<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Wildberries\Products\Controller\Admin\Barcode\Tests;

use BaksDev\Products\Category\Entity\ProductCategory;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries-products
 * @group wildberries-products-barcode
 */
#[When(env: 'test')]
final class NewControllerTest extends WebTestCase
{
    private const URL = '/admin/wb/barcode/new';

    private const ROLE = 'ROLE_WB_BARCODE_NEW';

    public function testRoleSuccessful(): void
    {

            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getModer(self::ROLE);

                $client->loginUser($usr, 'user');
                $client->request('GET', self::URL);

                self::assertResponseIsSuccessful();
            }

    }

    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {

            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getAdmin();

                $client->loginUser($usr, 'user');
                $client->request('GET', self::URL);

                self::assertResponseIsSuccessful();
            }

    }

    /**
     * Доступ по роли ROLE_USER
     */
    public function testRoleUserDeny(): void
    {

            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getUsr();
                $client->loginUser($usr, 'user');
                $client->request('GET', self::URL);

                self::assertResponseStatusCodeSame(403);
            }

    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {

            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $client->request('GET', self::URL);

                // Full authentication is required to access this resource
                self::assertResponseStatusCodeSame(401);
            }

    }

    public function testComplete(): void
    {
        self::assertTrue(true);
    }
}
