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
        /** @see .env.test */
        self::$Authorization = new WbAuthorizationToken(
            profile: new UserProfileUid($_SERVER['TEST_WILDBERRIES_PROFILE']),
            token: $_SERVER['TEST_WILDBERRIES_TOKEN'],
            warehouse: $_SERVER['TEST_WILDBERRIES_WAREHOUSE'] ?? null,
            percent: $_SERVER['TEST_WILDBERRIES_PERCENT'] ?? "0",
            card: $_SERVER['TEST_WILDBERRIES_CARD'] === "true" ?? false,
            stock: $_SERVER['TEST_WILDBERRIES_STOCK'] === "true" ?? false,
        );
    }

    public function testUseCase(): void
    {
        /** @var FindAllWbCategoryRequest $FindAllWbCategoryRequest */
        $FindAllWbCategoryRequest = self::getContainer()->get(FindAllWbCategoryRequest::class);
        $FindAllWbCategoryRequest->TokenHttpClient(self::$Authorization);

        /** @see WildberriesProductProperty */


        // 1 Одежда
        // 2 Обувь
        // 3 Аксессуары
        // 4 Белье
        /** 6 Головные уборы */
        // 7 Игрушки
        // 49 Красота
        // 200 Бижутерия
        // 204 Ювелирные украшения
        // 239 Спортивный товар
        // 257 Для праздника
        // 571 Канцелярские товары
        // 657 Бытовая техника
        // 723 Товары для животных
        // 739 Аксессуары для обуви
        // 760 Автозапчасти
        // 786 Книжная продукция и диски
        // 858 Оргтехника
        // 883 Товары для малышей
        // 1077 Хозяйственные товары
        // 1081 Спортивное питание и косметика
        // 1162 Ручной инструмент и оснастка
        // 1284 Рукоделие
        // 1465 Аксессуары для волос
        // 1513 Спортивная одежда
        // 1521 Техника для кухни
        // 1590 Посуда и инвентарь
        // 1598 Хранение вещей
        // 1605 Спортивные аксессуары
        // 1616 Шторы и аксессуары
        // 2050 Строительные материалы
        // 2051 Сантехника
        // 2207 Электротранспорт
        // 2240 Садовая техника
        // 2479 Мебель офисная и торговое оборудование
        // 2638 Детское питание
        // 3109 Продукты
        // 3324 Профессиональные музыкальные инструменты
        // 3497 Спортивная обувь
        // 4268 Здоровье
        // 4607 Одежда для малышей
        // 4735 Белье для малышей
        // 4823 Аксессуары для малышей
        // 5038 Товары для взрослых
        // 5495 Крупная бытовая техника
        // 6119 Спецодежда и СИЗы
        // 6237 Периферия и аксессуары
        // 6238 Ноутбуки и компьютеры
        // 6240 Автоэлектроника
        // 6249 Товары для курения
        // 6256 Игровые консоли и игры
        // 6257 Сетевое оборудование
        // 6258 Смартфоны и гаджеты
        // 6259 Умный дом и безопасность
        // 6260 Телевизоры и аудиотехника
        // 6261 Фото и Видеотехника
        // 6731 Мототовары
        // 6943 Все для садоводства
        // 6945 Товары для отдыха и кемпинга
        // 6946 Садовые инструменты и полив
        // 8555 Отделочные материалы
        // 8614 Бытовая химия
        // 8671 Ветаптека
        /** 8693 Текстиль для дома */
        // 8694 Освещение
        // 8695 Декор интерьера
        // 8745 Мебель для сна
        // 8746 Мебель корпусная и мебель для хранения
        // 8747 Мебель малых форм
        // 8748 Мебель садовая
        // 8751 Электроинструмент и оборудование
        // 8891 Автоаксессуары и дополнительное оборудование
        // 8892 Масла и технические жидкости
        // 8893 Шины и диски
        // 8894 Аккумуляторы для ТС
        // 8896 Автохимия и автокосметика
        // 8995 Аксессуары для электроники
        // 9021 Лотереи
        // 9081 Электрика
        // 9091 Мебель на заказ
        // 9114 Религия и эзотерика
        // 9213 Климатическая техника
        // 9214 Техника для красоты и здоровья



        $data = $FindAllWbCategoryRequest
            ->parent(1)
            ->findAll();

        foreach($data as $item)
        {
            self::assertIsInt($item->getId());
            self::assertIsString($item->getName());

            echo '// '.$item->getId().' '.$item->getName().PHP_EOL;
        }


        self::assertTrue(true);
    }

}