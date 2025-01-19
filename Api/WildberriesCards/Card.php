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

namespace BaksDev\Wildberries\Products\Api\WildberriesCards;

use ArrayObject;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use chillerlan\QRCode\Common\Mode;
use DateTimeImmutable;
use Symfony\Component\HttpClient\CachingHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\HttpCache\Store;

final class Card
{

    /**
     * Идентификатор карточки товара
     */
    private UserProfileUid $profile;


    /**
     * Идентификатор карточки товара
     */
    private int $id;

    /**
     * Артикул WB (номенклатура)
     */
    private int $nomenclature;

    /**
     * Раздел
     */
    private string $root;

    /**
     * Категория
     */
    private string $category;

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
     * Цвет
     */
    private string $color;


    private DateTimeImmutable $update;

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


    private bool $offer = true;


    public function __construct(UserProfileUid $profile, array $data)
    {
        //dd($data);

        //       if(empty($data['colors']))
        //       {
        //           throw new \Doctrine\Instantiator\Exception\InvalidArgumentException(
        //               sprintf('Не указан цвет для товара с номенклатурой %s', $data['nmID'])
        //           );
        //       }

        $this->profile = $profile;

        $this->id = $data['imtID'];
        $this->nomenclature = $data['nmID'];

        $this->name = $data['title'];
        $this->description = $data['description'] ?? null;
        $this->brand = $data['brand'];

        $this->category = $data['subjectName'];
        $this->article = $data['vendorCode'];
        $this->update = new DateTimeImmutable($data['updatedAt']);
        //$this->media = $data['photos'];

        //        foreach($data['characteristics'] as $characteristic) {
        //
        //        }

        //$this->color = current($data['colors']);

        $this->media = new ArrayObject();

        foreach($data['photos'] as $key => $photos)
        {
            $this->media->offsetSet($key, $photos['big'] ?: current($photos));
        }


        $this->characteristics = new ArrayObject();

        foreach($data['characteristics'] as $characteristic)
        {

            $key = mb_strtolower($characteristic['name']);

            $values = $characteristic['value'];
            $value = is_array($values) ? implode(', ', $values) : $values;

            $this->characteristics->offsetSet($key, $value);
        }


        $this->offers = new ArrayObject();

        foreach($data['sizes'] as $size)
        {

            if(empty($size['techSize']))
            {
                $this->offer = false;
            }

            $barcode = current($size['skus']);
            $this->offers->offsetSet($barcode, $size['techSize']);
        }
    }

    /*public function getCardDetail(): self
    {
        if(!$this->media)
        {
            throw new InvalidArgumentException(sprintf('В карточке отсутствуют медиа-файлы ( nomenclature: %s )', $this->nomenclature));
        }

        $client = HttpClient::create();
        $store = new Store(sys_get_temp_dir());
        $client = new CachingHttpClient($client, $store, ['default_ttl' => 86400]);


        $parseDomainCard = current($this->media)['big'];
        $DomainCard = substr($parseDomainCard, 0, strpos($parseDomainCard, '/images/')).'/info/ru/card.json';

        $response = $client->request('GET', $DomainCard);

        if($response->getStatusCode() !== 200)
        {
            throw new InvalidArgumentException(sprintf('Невозможно получить файл card.json ( url: %s ) ', $DomainCard));
        }

        $card = $response->toArray(false);


        $this->name = $card['imt_name'];
        $this->root = $card['subj_root_name'];
        $this->description = $card['description'] ?? '';


        $this->characteristics = new ArrayObject();

        foreach($card['options'] as $characteristic)
        {
            $this->characteristics->offsetSet($characteristic['name'], $characteristic['value']);
        }

        return $this;
    }*/

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
    public function getNomenclature(): int
    {
        return $this->nomenclature;
    }

    /**
     * Root
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * Category
     */
    public function getCategory(): string
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
    public function getDescription(): string
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
     * Общий артикул продукции при наличие торгового предложения
     */
    public function getBaseArticle(): string
    {
        $articleArr = explode("-", $this->article);
        array_pop($articleArr); // удаляем последний элемент массива артикула

        return implode("-", $articleArr);
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
     * Update
     */
    public function getUpdate(): DateTimeImmutable
    {
        return $this->update;
    }

    /**
     * Characteristics
     */
    public function getCharacteristicsCollection(): ArrayObject
    {
        return $this->characteristics;
    }

    public function getCharacteristic($name): ?string
    {
        if($this->characteristics->offsetExists($name))
        {
            return (string) $this->characteristics->offsetGet($name);
        }

        return null;
    }

    /**
     * Offers
     */

    public function isOffers(): bool
    {
        return $this->offer;
    }

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

    /**
     * Color
     */
    public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Profile
     */
    public function getProfile(): UserProfileUid
    {
        return $this->profile;
    }


}