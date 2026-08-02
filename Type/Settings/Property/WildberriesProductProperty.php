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

namespace BaksDev\Wildberries\Products\Type\Settings\Property;


use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;

final class WildberriesProductProperty
{
    public const string TYPE = 'wb_product_property';


    public const int CATEGORY_TIRE = 5283;  // Шины автомобильные

    /** Мебель */

    public const int CATEGORY_DESKS = 7611; // Столы письменные
    public const int CATEGORY_RACKS = 1901; // Стелажи

    /** Одежда */

    public const int CATEGORY_SHIRTS = 192; // Футболки
    public const int CATEGORY_SHIRTS_SPORT = 5217; // Спортивные футболки
    public const int CATEGORY_HOODIE = 1724; // Худи
    public const int CATEGORY_JEANS = 180; // Джинсы
    public const int CATEGORY_SVITSHOT = 159; // Свитшоты
    public const int CATEGORY_TOP = 185; // Топы


    public const int CATEGORY_SLIPPERS = 106; // Тапки;
    public const int CATEGORY_STRAPS = 107; // Шлепанцы;
    public const int CATEGORY_SABO = 98; // Cабо;
    public const int CATEGORY_CZECH = 1586; // Чешки;
    public const int CATEGORY_LONGSLEEVE = 217; // Лонгсливы
    public const int CATEGORY_SWEATERS = 163; //  -id: 163 -name: "Свитеры"


    // -id: 41 -name: "Блузки"
    // -id: 1635 -name: "Бомберы"
    // -id: 11 -name: "Брюки"
    // -id: 172 -name: "Ветровки"
    // -id: 153 -name: "Водолазки"
    //  -id: 215 -name: "Джемперы"
    //  -id: 177 -name: "Костюмы"
    //  -id: 161 -name: "Кофты"
    // -id: 168 -name: "Куртки"
    // -id: 149 -name: "Пиджаки"
    // -id: 162 -name: "Пижамы"
    //  -id: 69 -name: "Платья"
    // -id: 160 -name: "Пуловеры"
    //  -id: 184 -name: "Рубашки"
    //  -id: 233 -name: "Толстовки"
    // -id: 150 -name: "Туники"
    // -id: 219 -name: "Футболки-поло"
    // -id: 151 -name: "Шорты"
    // -id: 38 -name: "Юбки"

    /** 6 Головные уборы */

    // 84 Бейсболки
    // 83 Береты
    // 1722 Капоры
    // 2150 Кепи
    // 2817 Козырьки
    // 79 Наушники утепленные
    // 255 Панамы
    // 256 Повязки на голову
    // 2460 Фуражки
    // 4343 Хиджабы
    // 2651 Чалма
    public const int CATEGORY_CAP = 82; // 82 Шапки
    // 1863 Шапки-ушанки
    // 3529 Шапки-шлемы
    // 280 Шляпы


    /** 8693 Текстиль для дома */

    public const int CATEGORY_KITCHEN_APRONS = 402; // Фартуки кухонные
    public const int CATEGORY_MATTRESS_TOPPERS = 743; // 743 Наматрасники

    // 2846 Гамаки для ног
    // 4235 Держатели для балдахинов
    // 4747 Держатели для простыни
    // 5146 Дорожки кухонные
    // 2717 Зажимы для скатерти
    // 1995 Карманы на кровать
    // 1625 Коврики для ванной
    // 7707 Коврики для лестницы
    // 2316 Коврики для туалета
    // 3073 Коврики настольные
    // 8348 Коврики под компьютерное кресло
    // 2317 Коврики придверные
    // 7162 Ковровые дорожки
    // 7161 Ковры
    // 1492 Кольца для салфеток
    // 1358 Конверты для сервировки
    // 4946 Крепления для гамаков
    // 605 Наволочки
    // 1536 Наволочки декоративные
    // 2835 Наперники
    // 319 Одеяла
    // 321 Пледы
    // 1795 Плейсматы
    // 1045 Пододеяльники
    // 5229 Подстилки противоскользящие
    // 336 Подушки
    // 5317 Подушки внутренние декоративные
    // 570 Подушки декоративные
    // 4252 Подушки для ванны
    // 1136 Подушки на стул
    // 1182 Покрывала
    // 254 Полотенца банные
    // 1626 Полотенца кухонные
    // 2774 Полотенца-пончо
    // 197 Постельное белье
    // 401 Прихватки
    // 320 Простыни
    // 4161 Простыни одноразовые
    // 509 Салфетки сервировочные
    // 231 Скатерти
    // 1760 Фиксаторы для ковриков
    // 2776 Фиксаторы для одеяла
    // 1249 Чехлы для мебели
    // 1479 Шторы для ванной

    public const int CATEGORY_WORKERS_APRONS = 3188; // Фартуки рабочие;

    private ?WildberriesProductPropertyInterface $property = null; // Лонгслив;

    public function __construct(WildberriesProductPropertyInterface|self|string $property)
    {
        if(is_string($property) && class_exists($property))
        {
            $instance = new $property();

            if($instance instanceof WildberriesProductPropertyInterface)
            {
                $this->property = $instance;
                return;
            }
        }

        if($property instanceof WildberriesProductPropertyInterface)
        {
            $this->property = $property;
            return;
        }

        if($property instanceof self)
        {
            $this->property = $property->getWildberriesProductProperty();
            return;
        }

        /** @var WildberriesProductPropertyInterface $declare */
        foreach(self::getDeclared() as $declare)
        {
            if($declare::equals($property))
            {
                $this->property = new $declare();
                return;
            }
        }
    }

    public function getWildberriesProductProperty(): ?WildberriesProductPropertyInterface
    {
        return $this->property;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(WildberriesProductPropertyInterface::class, class_implements($className), true);
            },
        );
    }

    public function equals(mixed $property): bool
    {
        $property = new self($property);

        return $this->getWildberriesProductPropertyValue() === $property->getWildberriesProductPropertyValue();
    }

    public function getWildberriesProductPropertyValue(): ?string
    {
        return $this->property?->getIndex();
    }

    /** @see WbCharacteristicRequestTest */
    public static function caseCategory(): array
    {
        return [
            self::CATEGORY_TIRE => ['Шины', 'Шина', 'Шины автомобильные'],
            self::CATEGORY_SHIRTS => ['Футболки', 'Футболка'],
            self::CATEGORY_HOODIE => ['Худи'],
            self::CATEGORY_JEANS => ['Джинсы', 'Джинс'],
            self::CATEGORY_SVITSHOT => ['Свитшоты', 'Свитшот'],
            self::CATEGORY_TOP => ['Топы', 'Топ'],
            self::CATEGORY_KITCHEN_APRONS => ['Фартуки', 'Кухонные', 'Фартук', 'Кухонный'],
            self::CATEGORY_WORKERS_APRONS => ['Фартуки', 'Рабочие', 'Фартук', 'Рабочий'],
            self::CATEGORY_SLIPPERS => ['Тапки', 'Тапочки', 'Домашние'],
            self::CATEGORY_STRAPS => ['Шлепанцы'],
            self::CATEGORY_SABO => ['Cабо'],
            self::CATEGORY_SHIRTS_SPORT => ['Футболки', 'Футболка спортивная'],
            self::CATEGORY_CZECH => ['Чешки'],
            self::CATEGORY_LONGSLEEVE => ['Лонгслив', 'Лонгсливы'],
            self::CATEGORY_RACKS => ['Стеллаж', 'Стеллажи'],
            self::CATEGORY_DESKS => ['Стол', 'Столы'],
            self::CATEGORY_MATTRESS_TOPPERS => ['Наматрасник', 'Наматрасники'],
            self::CATEGORY_CAP => ['Шапка', 'Шапки'],
        ];
    }

    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $property)
        {
            /** @var WildberriesProductPropertyInterface $property */
            $class = new $property();
            $case[$class::priority()] = new self($class);
        }

        return $case;
    }

    public function __toString(): string
    {
        return $this->property ? $this->property->getIndex() : '';
    }


}
