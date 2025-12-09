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

namespace BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;

final class WildberriesProductsCardResult
{
    private array|null|false $product_params_decoded = null;

    private array|null|false $product_property_decoded = null;

    private array|null|false $product_size_decoded = null;

    private array|null|false $product_article_decoded = null;

    public function __construct(
        private readonly string $product_uid,
        private readonly string $product_images,

        private readonly int $product_price,
        private readonly ?int $product_old_price,
        private readonly string $profile,
        private readonly ?string $product_card,

        private readonly ?string $article,
        private readonly ?string $card_article,

        private readonly ?string $offer_const,
        private readonly ?string $product_offer_value,
        private readonly ?string $product_offer_postfix,

        private readonly ?string $variation_const,
        private readonly ?string $product_variation_value,
        private readonly ?string $product_variation_postfix,

        private readonly ?string $modification_const,
        private readonly ?string $product_modification_value,
        private readonly ?string $product_modification_postfix,

        private readonly ?string $product_size,
        private readonly ?string $product_name,
        private readonly ?string $product_preview,
        private readonly ?string $category_name,
        private readonly ?int $length,
        private readonly ?int $width,
        private readonly ?int $height,
        private readonly ?int $weight,
        private readonly ?string $product_property,
        private readonly ?string $product_params,
        private readonly ?string $product_currency,
        private readonly ?string $product_quantity,
        private readonly ?int $market_category,
    ) {}

    public function getProductUid(): ProductUid
    {
        return new ProductUid($this->product_uid);
    }

    public function getProductImages(): string
    {
        return $this->product_images;
    }

    public function getProductPrice(): Money
    {
        return $this->product_old_price ? new Money($this->product_price, true) : new Money(0);
    }

    public function getProductOldPrice(): Money|false
    {
        return $this->product_old_price ? new Money($this->product_old_price, true) : false;
    }

    public function getProductCard(): ?string
    {
        return $this->product_card;
    }

    public function getCardArticle(): ?string
    {
        return $this->card_article;
    }

    public function getArticle(): array|false
    {
        if(false === is_string($this->article) || false === json_validate($this->article))
        {
            return false;
        }

        if(is_null($this->product_article_decoded))
        {
            $product_article_decoded = json_decode($this->article, false, 512, JSON_THROW_ON_ERROR);

            if(true === empty($product_article_decoded))
            {
                $this->product_article_decoded = false;

                return false;
            }

            $this->product_article_decoded = $product_article_decoded;
        }

        return $this->product_article_decoded;
    }

    /**
     * ProductOffer
     */

    public function getOfferConst(): ProductOfferConst
    {
        return new ProductOfferConst($this->offer_const);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    /**
     * ProductVariation
     */

    public function getVariationConst(): ?ProductVariationConst
    {
        return null === $this->variation_const ? null : new ProductVariationConst($this->variation_const);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }


    /**
     * ProductModification
     */

    public function getModificationConst(): ?ProductModificationConst
    {
        return null === $this->modification_const ? null : new ProductModificationConst($this->variation_const);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getModelName(): string|false
    {
        $category_name = explode(' ', $this->category_name);

        $model = $this->product_name;

        foreach($category_name as $category)
        {
            $model = str_replace($category, '', $model);
            $model = trim($model);
        }

        return empty($model) || $model === $this->product_name ? false : $model;
    }


    public function getProductSize(): array|false
    {
        if(false === is_string($this->product_size) || false === json_validate($this->product_size))
        {
            return false;
        }

        if(is_null($this->product_size_decoded))
        {
            $product_size_decoded = json_decode($this->product_size, false, 512, JSON_THROW_ON_ERROR);

            if(true === empty($product_size_decoded))
            {
                $this->product_size_decoded = false;

                return false;
            }

            $this->product_size_decoded = $product_size_decoded;
        }

        return $this->product_size_decoded;
    }

    public function getProductName(): ?string
    {
        return $this->product_name;
    }

    public function getProductPreview(): ?string
    {
        return $this->product_preview;
    }

    public function getCategoryName(): ?string
    {
        return $this->category_name;
    }

    public function getLength(): ?int
    {
        return (int) ($this->length / 10);
    }

    public function getWidth(): ?int
    {
        return (int) ($this->width / 10);
    }

    public function getHeight(): ?int
    {
        return (int) ($this->height / 10);
    }

    public function getWeight(): ?int
    {
        return (int) ($this->weight / 100);
    }

    public function getProductProperty(): array|false
    {
        if(false === is_string($this->product_property) || false === json_validate($this->product_property))
        {
            return false;
        }

        if(is_null($this->product_property_decoded))
        {
            $product_property_decoded = json_decode($this->product_property, false, 512, JSON_THROW_ON_ERROR);

            if(true === empty($product_property_decoded))
            {
                $this->product_property_decoded = false;

                return false;
            }

            $this->product_property_decoded = $product_property_decoded;
        }

        return $this->product_property_decoded;
    }

    public function getProductParams(): array|false
    {
        if(false === is_string($this->product_params) || false === json_validate($this->product_params))
        {
            return false;
        }

        if(is_null($this->product_params_decoded))
        {
            $product_params_decoded = json_decode($this->product_params, false, 512, JSON_THROW_ON_ERROR);

            if(true === empty($product_params_decoded))
            {
                $this->product_params_decoded = false;

                return false;
            }

            $this->product_params_decoded = $product_params_decoded;
        }

        return $this->product_params_decoded;
    }

    public function getProductCurrency(): ?string
    {
        return $this->product_currency;
    }

    public function getProductQuantity(): int
    {
        if(empty($this->product_quantity))
        {
            return 0;
        }

        if(false === json_validate($this->product_quantity))
        {
            return 0;
        }

        $decode = json_decode($this->product_quantity, false, 512, JSON_THROW_ON_ERROR);

        $quantity = 0;

        foreach($decode as $item)
        {
            $quantity += $item->total;
            $quantity -= $item->reserve;
        }

        return max($quantity, 0);
    }

    public function getMarketCategory(): ?int
    {
        return $this->market_category;
    }

    public function getSearchArticle(): ?string
    {
        $articles = $this->getArticle();

        /** Артикул шин публикуем как есть */
        if($this->market_category === WildberriesProductProperty::CATEGORY_TIRE)
        {
            return $articles ? current($articles) : null;
        }

        if(false !== $articles)
        {
            $article = $articles[0];

            // Находим позицию последнего дефиса
            $pos = strrpos($article, '-');

            if($pos !== false)
            {
                // Обрезаем строку до последнего дефиса
                $article = substr($article, 0, $pos);
            }
        }

        return $article ?? null;
    }

    public function getProfile(): string
    {
        return $this->profile;
    }

    /** Метод проверяет свойства, без которых нельзя получить карточку */
    public function isCredentials(): bool
    {
        if(empty($this->product_price))
        {
            return false;
        }

        if(empty($this->length))
        {
            return false;
        }

        if(empty($this->width))
        {
            return false;
        }

        if(empty($this->height))
        {
            return false;
        }

        if(empty($this->weight))
        {
            return false;
        }

        return true;
    }
}