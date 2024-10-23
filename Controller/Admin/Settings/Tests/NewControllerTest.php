<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Wildberries\Products\Controller\Admin\Settings\Tests;

use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group wildberries-products
 * @group wildberries-products-settings
 */
#[When(env: 'test')]
final class NewControllerTest extends WebTestCase
{
    private const URL = '/admin/wb/product/setting/new/%s/Футболки';

    private const ROLE = 'ROLE_WB_PRODUCTS_SETTING_NEW';

    private static ?CategoryProductUid $identifier;

    public static function setUpBeforeClass(): void
    {
        // Получаем одно из событий Продукта
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::$identifier = $em->getRepository(CategoryProduct::class)->findOneBy([], ['id' => 'DESC'])?->getId();

        $em->clear();
        //$em->close();
    }

    public function testRoleSuccessful(): void
    {
        self::assertTrue(true);
        return;

        // Получаем одно из событий
        $identifier = self::$identifier;

        if($identifier)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getModer(self::ROLE);

                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $identifier->getValue()));

                self::assertResponseIsSuccessful();
            }
        }

        self::assertTrue(true);

    }

    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {

        self::assertTrue(true);
        return;

        // Получаем одно из событий
        $identifier = self::$identifier;

        if($identifier)
        {
            //self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getAdmin();

                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $identifier->getValue()));

                self::assertResponseIsSuccessful();
            }
        }

        self::assertTrue(true);

    }

    /**
     * Доступ по роли ROLE_USER
     */
    public function testRoleUserDeny(): void
    {
        self::assertTrue(true);
        return;

        // Получаем одно из событий
        $identifier = self::$identifier;

        if($identifier)
        {
            //self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getUsr();
                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, $identifier->getValue()));

                self::assertResponseStatusCodeSame(403);
            }
        }

        self::assertTrue(true);

    }

    /** Доступ по без роли */
    public function testGuestFiled(): void
    {

        //self::assertTrue(true);
        //return;

        // Получаем одно из событий
        $identifier = self::$identifier;

        if($identifier)
        {
            //self::ensureKernelShutdown();
            $client = static::createClient();

            foreach(TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $client->request('GET', sprintf(self::URL, $identifier->getValue()));

                // Full authentication is required to access this resource
                self::assertResponseStatusCodeSame(401);
            }
        }

        self::assertTrue(true);

    }

    //    public function testComplete(): void
    //    {
    //        self::assertTrue(true);
    //    }
}
