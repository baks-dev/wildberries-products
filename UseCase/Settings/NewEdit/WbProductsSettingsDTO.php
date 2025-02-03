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

namespace BaksDev\Wildberries\Products\UseCase\Settings\NewEdit;


use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Wildberries\Products\Entity\Settings\Event\WbProductSettingsEventInterface;
use BaksDev\Wildberries\Products\Type\Settings\Event\WbProductSettingsEventUid;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Invariable\WbProductsSettingsInvariableDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see WbProductSettingsEvent */
final class WbProductsSettingsDTO implements WbProductSettingsEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?WbProductSettingsEventUid $id = null;

    /**
     * ID настройки
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly CategoryProductUid $main;


    /**
     * Коллекция Свойств
     */
    #[Assert\Valid]
    private ArrayCollection $property;


    /**
     * Характеристики
     */
    #[Assert\Valid]
    private ArrayCollection $parameter;

    private WbProductsSettingsInvariableDTO $invariable;

    //    /** Коллекция торговых предложений */
    //    private ArrayCollection $offer;
    //
    //    /** Коллекция вариантов в торговом предложении */
    //    private ArrayCollection $variation;


    //    private ConfigCardDTO $config;
    //
    //private ?FieldUid $country = null;

    public function __construct()
    {
        $this->property = new ArrayCollection();
        $this->parameter = new ArrayCollection();

        $this->invariable = new WbProductsSettingsInvariableDTO();

        //        $this->offer = new ArrayCollection();
        //        $this->variation = new ArrayCollection();
    }

    /**
     * Идентификатор события
     */
    public function getEvent(): ?WbProductSettingsEventUid
    {
        return $this->id;
    }


    public function setId(WbProductSettingsEventUid $id): void
    {
        $this->id = $id;
    }


    /**
     * ID настройки
     */
    public function getMain(): ?CategoryProductUid
    {
        return $this->main;
    }


    public function setMain(CategoryProductUid $main): void
    {
        $this->main = $main;
    }


    /**
     * Категория Wildberries
     */
    public function setCategory(int $category): self
    {
        $this->invariable->setCategory($category);
        return $this;
    }

    public function getCategory(): int
    {
        return $this->invariable->getCategory();
    }


    /* PROPERTY */

    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }


    public function addProperty(Property\WbProductSettingsPropertyDTO $property): self
    {

        $filter = $this->property->filter(function(Property\WbProductSettingsPropertyDTO $element) use ($property) {
            return $element->getType() === $property->getType();
        });

        if($filter->isEmpty())
        {
            $this->property->add($property);
        }

        return $this;
    }


    public function removeProperty(Property\WbProductSettingsPropertyDTO $property): void
    {
        $this->property->removeElement($property);
    }


    /* PARAMETERS */

    public function getParameter(): ArrayCollection
    {
        return $this->parameter;
    }

    public function addParameter(Parameters\WbProductSettingsParametersDTO $parameter): self
    {
        $filter = $this->parameter->filter(function(Parameters\WbProductSettingsParametersDTO $element) use ($parameter
        ) {
            return $element->getType() === $parameter->getType();
        });

        if($filter->isEmpty())
        {
            $this->parameter->add($parameter);
        }

        return $this;
    }

    public function removeParameter(Parameters\WbProductSettingsParametersDTO $parameter): void
    {
        $this->parameter->removeElement($parameter);
    }

    /**
     * Invariable
     */
    public function getInvariable(): WbProductsSettingsInvariableDTO
    {
        return $this->invariable;
    }


}