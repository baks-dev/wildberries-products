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

    private ?WildberriesProductPropertyInterface $property = null;

    public const int CATEGORY_TIRE = 5283; // Шины автомобильные
    public const int CATEGORY_SHIRTS = 192; // Футболки
    public const int CATEGORY_HOODIE = 1724; // Худи
    public const int CATEGORY_JEANS = 180; // Джинсы
    public const int CATEGORY_SVITSHOT = 159; // Свитшоты
    public const int CATEGORY_TOP = 185; // Топы

    /** @see WbCharacteristicRequestTest */
    public static function caseCategory(): array
    {
        return [
            self::CATEGORY_TIRE => ['Шины', 'Шина', 'Шины автомобильные'],
            self::CATEGORY_SHIRTS => ['Футболки', 'Футболка'],
            self::CATEGORY_HOODIE => ['Худи'],
            self::CATEGORY_JEANS => ['Джинсы', 'Джинс'],
            self::CATEGORY_SVITSHOT => ['Свитшот', 'Свитшоты'],
            self::CATEGORY_TOP => ['Топ', 'Топы'],
        ];
    }

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


    public function __toString(): string
    {
        return $this->property ? $this->property->getIndex() : '';
    }

    public function getWildberriesProductProperty(): ?WildberriesProductPropertyInterface
    {
        return $this->property;
    }

    public function getWildberriesProductPropertyValue(): ?string
    {
        return $this->property?->getIndex();
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

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function($className) {
                return in_array(WildberriesProductPropertyInterface::class, class_implements($className), true);
            }
        );
    }

    public function equals(mixed $property): bool
    {
        $property = new self($property);

        return $this->getWildberriesProductPropertyValue() === $property->getWildberriesProductPropertyValue();
    }


}
