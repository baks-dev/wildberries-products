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

namespace BaksDev\Wildberries\Products\Entity\Settings\Event;


use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Wildberries\Products\Entity\Settings\Invariable\WbProductSettingsInvariable;
use BaksDev\Wildberries\Products\Entity\Settings\Modify\WbProductSettingsModify;
use BaksDev\Wildberries\Products\Entity\Settings\Name\WbProductSettingsName;
use BaksDev\Wildberries\Products\Entity\Settings\Parameters\WbProductSettingsParameters;
use BaksDev\Wildberries\Products\Entity\Settings\Property\WbProductSettingsProperty;
use BaksDev\Wildberries\Products\Entity\Settings\WbProductSettings;
use BaksDev\Wildberries\Products\Type\Settings\Event\WbProductSettingsEventUid;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* Event */

#[ORM\Entity]
#[ORM\Table(name: 'wb_card_settings_event')]
class WbProductSettingsEvent extends EntityEvent
{
    /**
     * Идентификатор события
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: WbProductSettingsEventUid::TYPE)]
    private WbProductSettingsEventUid $id;

    /**
     * Идентификатор настройки
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: CategoryProductUid::TYPE)]
    private CategoryProductUid $main;

    /** One To One */
    #[ORM\OneToOne(targetEntity: WbProductSettingsInvariable::class, mappedBy: 'event', cascade: ['all'])]
    private ?WbProductSettingsInvariable $invariable = null;

    /**
     * Модификатор
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: WbProductSettingsModify::class, mappedBy: 'event', cascade: ['all'])]
    private WbProductSettingsModify $modify;

    /**
     * Свойства карточки
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: WbProductSettingsProperty::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $property;


    /**
     * Характеристики карточки
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: WbProductSettingsParameters::class, mappedBy: 'event', cascade: ['all'])]
    private Collection $parameter;


    public function __construct()
    {
        $this->id = new WbProductSettingsEventUid();
        $this->modify = new WbProductSettingsModify($this);
    }

    public function __clone(): void
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): WbProductSettingsEventUid
    {
        return $this->id;
    }

    public function setMain(WbProductSettings $settings): self
    {
        return $this;
    }

    public function getMain(): CategoryProductUid
    {
        return $this->main;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof WbProductSettingsEventInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    public function setEntity($dto): mixed
    {
        if($dto instanceof WbProductSettingsEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }




}