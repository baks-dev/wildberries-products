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

namespace BaksDev\Wildberries\Products\Entity\Barcode\Event;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Custom\WbBarcodeCustom;
use BaksDev\Wildberries\Products\Entity\Barcode\Modify\WbBarcodeModify;
use BaksDev\Wildberries\Products\Entity\Barcode\Property\WbBarcodeProperty;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;
use BaksDev\Wildberries\Products\Type\Barcode\Event\WbBarcodeEventUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'wb_barcode_event')]
class WbBarcodeEvent extends EntityEvent
{
    /** ID События */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: WbBarcodeEventUid::TYPE)]
    private WbBarcodeEventUid $id;

    /**
     * ID категории
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $main;

    /**
     * Добавить Торговое предложение в стикер
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $offer = false;

    /**
     * Добавить Множественный вариант в стикер
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $variation = false;

    /**
     * Количество стикеров
     */
    #[Assert\NotBlank]
    #[Assert\Range(min: 1, max: 5)]
    #[ORM\Column(type: Types::SMALLINT)]
    private int $counter = 1;

    /** Коллекция свойств продукта */
    #[Assert\Valid]
    #[ORM\OrderBy(['sort' => 'ASC'])]
    #[ORM\OneToMany(targetEntity: WbBarcodeProperty::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $property;

    /** Коллекция пользовательских свойств */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: WbBarcodeCustom::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $custom;

    /** Модификатор */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: WbBarcodeModify::class, mappedBy: 'event', cascade: ['all'])]
    private WbBarcodeModify $modify;

    public function __construct()
    {
        $this->id = new WbBarcodeEventUid();
        $this->modify = new WbBarcodeModify($this);
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): WbBarcodeEventUid
    {
        return $this->id;
    }

    public function getMain(): CategoryProductUid
    {
        return $this->main;
    }

    public function setMain(WbBarcode|CategoryProductUid $main): self
    {
        if($main instanceof WbBarcode)
        {
            return $this;
        }

        $this->main = $main;

        return $this;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof WbBarcodeEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof WbBarcodeEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


}