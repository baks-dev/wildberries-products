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

namespace BaksDev\Wildberries\Products\Api\Settings\Characteristic\Tests;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Api\Settings\Characteristic\FindAllWbCharacteristicRequest;
use BaksDev\Wildberries\Products\Api\Settings\Characteristic\WbCharacteristicDTO;
use BaksDev\Wildberries\Products\Mapper\Params\WildberriesProductParametersCollection;
use BaksDev\Wildberries\Products\Mapper\Params\WildberriesProductParametersInterface;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use BaksDev\Wildberries\Type\Authorization\WbAuthorizationToken;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


/**
 * @group wildberries-products
 */
#[When(env: 'test')]
class WbCharacteristicRequestTest extends KernelTestCase
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
        /** @var FindAllWbCharacteristicRequest $WbCharacteristicRequest */
        $WbCharacteristicRequest = self::getContainer()->get(FindAllWbCharacteristicRequest::class);
        $WbCharacteristicRequest->TokenHttpClient(self::$Authorization);

        //

        $cats = [
            WildberriesProductProperty::CATEGORY_TIRE, // Шины автомобильные
            WildberriesProductProperty::CATEGORY_SHIRTS, // Футболки
            WildberriesProductProperty::CATEGORY_HOODIE, // Худи
            WildberriesProductProperty::CATEGORY_JEANS, // Джинсы
            WildberriesProductProperty::CATEGORY_SVITSHOT, // Свитшоты
            WildberriesProductProperty::CATEGORY_TOP, // Свитшоты
            WildberriesProductProperty::CATEGORY_KITCHEN_APRONS, // Фартуки кухонные
            WildberriesProductProperty::CATEGORY_SLIPPERS, // Тапки
            WildberriesProductProperty::CATEGORY_STRAPS,// Шлепанцы;
            WildberriesProductProperty::CATEGORY_SABO, // Cабо;
        ];

        /** @see WildberriesProductProperty */

        $cats = [WildberriesProductProperty::CATEGORY_KITCHEN_APRONS];

        foreach($cats as $category)
        {
            $data = $WbCharacteristicRequest
                ->category($category)
                ->findAll();

            /** @var WildberriesProductParametersCollection $WildberriesProductParamsCollection */
            $WildberriesProductParamsCollection = self::getContainer()->get(WildberriesProductParametersCollection::class);
            $params = $WildberriesProductParamsCollection->cases($category);

            /** @var WbCharacteristicDTO $item */

            $count = 1;
            foreach($data as $i => $item)
            {
                self::assertNotFalse($params,
                    sprintf('Отсутствует элемент ID: %s ( %s ) для категории %s', $item->getId(), $item->getName(), $category)
                );

                self::assertNotEmpty(array_filter($params, function(WildberriesProductParametersInterface $param) use (
                    $item
                ) {
                    return $param->equals($item->getId());
                }), sprintf('Отсутствует элемент ID: %s ( %s ) для категории %s', $item->getId(), $item->getName(), $category));

                ++$count;
            }


            self::assertCount($count, $params, message: sprintf('Количество элементов %s при %s параметрах', $count, count($params)));
        }

        self::assertTrue(true);

    }

}