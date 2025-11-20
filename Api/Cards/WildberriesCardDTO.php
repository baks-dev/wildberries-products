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

namespace BaksDev\Wildberries\Products\Api\Cards;

use ArrayObject;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;


final class WildberriesCardDTO
{
    private UserProfileUid $profile;

    /**
     * ID карточки
     * Артикулы WB из одной карточки товара будут иметь одинаковый imtID
     */
    private int $id;

    /**
     * Идентификатор товара (Артикул WB)
     */
    private int $nomenclature;


    /**
     * Категория
     */
    private int $category;

    /**
     * Название товара
     */
    private string $name;

    /**
     * Описание товар
     */
    private ?string $description;

    /**
     * Артикул продавца
     */
    private string $article;

    /**
     * Брэнд
     */
    private string $brand;


    /**
     * Медиафайлы номенклатуры.
     */
    private ArrayObject $media;

    /**
     * Характеристики товара
     */
    private ArrayObject $characteristics;

    /**
     * Торговые предложения
     */
    private ArrayObject $offers;

    private ArrayObject $dimensions;

    private ArrayObject $chrt;


    public function __construct(array $data, UserProfileUid $profile)
    {

        $this->profile = $profile;

        $this->id = $data['nmID'];
        $this->nomenclature = $data['imtID'];

        $this->name = $data['title'];
        $this->description = $data['description'] ?? null;
        $this->brand = $data['brand'];

        $this->category = $data['subjectID'];
        $this->article = $data['vendorCode'];

        $this->media = new ArrayObject();

        if(false === empty($data['photos']))
        {
            foreach($data['photos'] as $key => $photos)
            {
                $this->media->offsetSet($key, $photos['big'] ?: current($photos));
            }
        }

        $this->characteristics = new ArrayObject();

        foreach($data['characteristics'] as $characteristic)
        {
            $values = $characteristic['value'];
            $value = is_array($values) ? implode(', ', $values) : $values;

            $this->characteristics->offsetSet((string) $characteristic['id'], $value);
        }


        $this->characteristics->offsetSet('brand', $data['brand']);
        $this->characteristics->offsetSet('title', $data['title']);


        $this->offers = new ArrayObject();
        $this->chrt = new ArrayObject();


        // по умолчанию размеры присваиваем из techSize
        $keySize = 'techSize';

        // Для определенных категорий присваиваем wbSize
        if(
            in_array($this->category, [
                WildberriesProductProperty::CATEGORY_CZECH, // Чешки
            ])
        )
        {
            $keySize = 'wbSize';
        }

        foreach($data['sizes'] as $size)
        {
            if(false === isset($size[$keySize]))
            {
                break;
            }

            $barcode = current($size['skus']);
            $this->offers->offsetSet($barcode, $size[$keySize]);
            $this->chrt->offsetSet($size[$keySize], $size['chrtID']);
        }

        $this->dimensions = new ArrayObject($data['dimensions']);
    }

    /**
     * Characteristics
     */
    public function getCharacteristicsCollection(): ArrayObject
    {
        return $this->characteristics;
    }

    public function getCharacteristic($id): ?string
    {
        if($this->characteristics->offsetExists($id))
        {
            return (string) $this->characteristics->offsetGet($id);
        }

        return null;
    }


    /**
     * Параметры упаковки
     */

    public function getWidth(): ?int
    {
        if($this->dimensions->offsetExists('width'))
        {
            return (int) $this->dimensions->offsetGet('width') * 10;
        }

        return null;
    }

    public function getHeight(): ?int
    {
        if($this->dimensions->offsetExists('height'))
        {
            return (int) $this->dimensions->offsetGet('height') * 10;
        }

        return null;
    }

    public function getLength(): ?int
    {
        if($this->dimensions->offsetExists('length'))
        {
            return (int) $this->dimensions->offsetGet('length') * 10;
        }

        return null;
    }


    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }

    /**
     * Id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Nomenclature
     */
    public function getNomenclature(): mixed
    {
        return $this->nomenclature;
    }

    /**
     * Category
     */
    public function getCategory(): int
    {
        return $this->category;
    }

    /**
     * Name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Description
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Article
     */
    public function getArticle(): string
    {
        return $this->article;
    }

    /**
     * Brand
     */
    public function getBrand(): string
    {
        return $this->brand;
    }

    /**
     * Media
     */
    public function getMedia(): ArrayObject
    {
        return $this->media;
    }

    public function countMedia(): int
    {
        return count($this->media);
    }

    /**
     * Characteristics
     */
    public function getCharacteristics(): ArrayObject
    {
        return $this->characteristics;
    }


    /**
     * Offers
     */


    public function getOffersCollection(): ArrayObject
    {
        return $this->offers;
    }

    public function getCurrentBarcode(): string
    {
        $iterator = $this->offers->getIterator();
        $iterator->rewind();

        return (string) $iterator->key();
    }

    public function getCurrentValue(): mixed
    {
        $iterator = $this->offers->getIterator();
        $iterator->rewind();

        return $iterator->current();
    }


    public function getOffer($barcode): string
    {
        return $this->offers->offsetGet($barcode);
    }

    // $size - '4XL'
    public function getChrt(string $size): int|false
    {
        return $this->chrt->offsetGet($size) ?: false;
    }
}