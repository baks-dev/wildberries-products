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

namespace BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit;


use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEventInterface;
use BaksDev\Wildberries\Products\Type\Barcode\Event\WbBarcodeEventUid;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Counter\WbBarcodeCounterDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Modification\WbBarcodeModificationDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Name\WbBarcodeNameDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Offer\WbBarcodeOfferDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Variation\WbBarcodeVariationDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see WbBarcodeEvent */
final class WbBarcodeDTO implements WbBarcodeEventInterface
{
    /**
     * Идентификатор события
     */
    #[Assert\Uuid]
    private ?WbBarcodeEventUid $id = null;


    /**
     * Идентификатор категории
     */
    #[Assert\Uuid]
    #[Assert\NotBlank]
    private ?CategoryProductUid $main = null;

    /**
     * Флаг для запрета редактирования категории
     */
    private bool $hidden = false;


    /**
     * Количество стикеров
     */
    #[Assert\Valid]
    private WbBarcodeCounterDTO $counter;

    /**
     * Свойства товара
     */
    #[Assert\Valid]
    private ArrayCollection $property;

    /**
     * Пользовательские свойства
     */
    #[Assert\Valid]
    private ArrayCollection $custom;

    /**
     * Флаг отображения звания в стикере
     */
    private WbBarcodeNameDTO $name;


    /**
     * Добавить Торговое предложение в стикер
     */
    #[Assert\Valid]
    private WbBarcodeOfferDTO $offer;

    /**
     * Добавить Множественный вариант в стикер
     */
    #[Assert\Valid]
    private WbBarcodeVariationDTO $variation;

    /**
     * Добавить Модификатор множественного варианта
     */
    #[Assert\Valid]
    private WbBarcodeModificationDTO $modification;


    public function __construct()
    {
        $this->property = new ArrayCollection();
        $this->custom = new ArrayCollection();

        $this->counter = new WbBarcodeCounterDTO();
        $this->name = new WbBarcodeNameDTO();
        $this->offer = new WbBarcodeOfferDTO();
        $this->variation = new WbBarcodeVariationDTO();
        $this->modification = new WbBarcodeModificationDTO();
    }

    public function getEvent(): ?WbBarcodeEventUid
    {
        return $this->id;
    }

    public function setId(WbBarcodeEventUid $id): void
    {
        $this->id = $id;
    }


    public function getMain(): ?CategoryProductUid
    {
        return $this->main;
    }


    public function setMain(string|CategoryProductUid $category): void
    {

        $this->main = $category instanceof CategoryProductUid ? $category : new CategoryProductUid($category);
    }

    public function hiddenCategory(): void
    {
        $this->hidden = true;
    }

    public function isHiddenCategory(): bool
    {
        return $this->hidden;
    }


    public function getProperty(): ArrayCollection
    {
        return $this->property;
    }

    public function addProperty(Property\WbBarcodePropertyDTO $property): void
    {
        $this->property->add($property);
    }

    public function removeProperty(Property\WbBarcodePropertyDTO $property): void
    {
        $this->property->removeElement($property);
    }

    public function getPropertyClass(): Property\WbBarcodePropertyDTO
    {
        return new Property\WbBarcodePropertyDTO();
    }

    public function getCounter(): WbBarcodeCounterDTO
    {
        return $this->counter;
    }

    /** CUSTOM */

    public function getCustom(): ArrayCollection
    {
        return $this->custom;
    }

    public function addCustom(Custom\WbBarcodeCustomDTO $custom): void
    {
        $this->custom->add($custom);
    }

    public function removeCustom(Custom\WbBarcodeCustomDTO $custom): void
    {
        $this->custom->removeElement($custom);
    }

    public function getName(): WbBarcodeNameDTO
    {
        return $this->name;
    }

    public function getOffer(): WbBarcodeOfferDTO
    {
        return $this->offer;
    }

    public function getVariation(): WbBarcodeVariationDTO
    {
        return $this->variation;
    }

    public function getModification(): WbBarcodeModificationDTO
    {
        return $this->modification;
    }

}

