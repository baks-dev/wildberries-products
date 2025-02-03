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

namespace BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Property;

use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Wildberries\Products\Entity\Settings\Property\WbProductSettingsPropertyInterface;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use Doctrine\DBAL\Types\Types;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see WbProductSettingsProperty */
final class WbProductSettingsPropertyDTO implements WbProductSettingsPropertyInterface
{
    /**
     * Наименование характеристики
     */
    private ?string $name = null;

    /**
     * Идентификатор характеристики
     */
    #[Assert\NotBlank]
    private ?WildberriesProductProperty $type = null;



    /**
     * Связь на свойство продукта в категории
     */
    #[Assert\Uuid]
    private ?CategoryProductSectionFieldUid $field = null;


    /**
     * Значение по умолчанию
     */
    private ?string $def = null;


    /* Вспомогательные свойства */

    /**
     * Характеристика обязательна к заполнению
     */
    private readonly bool $required;

    /**
     * Единица измерения (см, гр и т.д.)
     */
    private readonly string $unit;

    /**
     * Характеристика популярна у пользователей
     */
    private readonly bool $popular;


    /**
     * Name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getType(): WildberriesProductProperty
    {
        return $this->type;
    }


    public function setType(WildberriesProductProperty $type): self
    {
        $this->type = $type;
        return $this;
    }


    public function getField(): ?CategoryProductSectionFieldUid
    {
        return $this->field;
    }


    public function setField(?CategoryProductSectionFieldUid $field): void
    {
        $this->field = $field;
    }

    /**
     * Def
     */
    public function getDef(): ?string
    {
        return $this->def;
    }

    public function setDef(?string $def): self
    {
        $this->def = $def;
        return $this;
    }






    /**
     * Характеристика обязательна к заполнению
     */
    public function isRequired(): bool
    {

        if(false === (new ReflectionProperty(self::class, 'required')->isInitialized($this)))
        {
            $this->required = true;
        }

        return $this->required;
    }


    public function setRequired(bool $required): self
    {
        $this->required = $required;
        return $this;
    }


    /**
     * Единица измерения (см, гр и т.д.)
     */
    public function getUnit(): ?string
    {

        if(false === (new ReflectionProperty(self::class, 'unit')->isInitialized($this)))
        {
            $this->unit = '';
        }

        return $this->unit;
    }


    public function setUnit(?string $unit): void
    {
        $this->unit = $unit;
    }


    /**
     * Popular
     */
    public function isPopular(): bool
    {

        if(false === (new ReflectionProperty(self::class, 'popular')->isInitialized($this)))
        {
            $this->popular = false;
        }

        return $this->popular;
    }


    public function setPopular(bool $popular): void
    {
        $this->popular = $popular;
    }

}