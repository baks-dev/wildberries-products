<?php
/*
 * Copyright 2025.  Baks.dev <admin@baks.dev>
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

final readonly class WildberriesProductsCardResult
{
    public function __construct(
        private string $product_uid,
        private string $product_images,
        private int $product_old_price,
        private string $profile,
        private ?string $product_card,
        private ?string $article,
        private ?string $offer_const,
        private ?string $product_offer_value,
        private ?string $product_offer_postfix,
        private ?string $variation_const,
        private ?string $product_variation_value,
        private ?string $product_variation_postfix,
        private ?string $product_size,
        private ?string $product_name,
        private ?string $product_preview,
        private ?string $category_name,
        private ?int $length,
        private ?int $width,
        private ?int $height,
        private ?int $weight,
        private ?string $product_property,
        private ?string $product_params,
        private ?string $product_currency,
        private ?int $product_quantity,
        private ?int $market_category,
    ) {}

    public function getProductUid(): ProductUid
    {
        return new ProductUid($this->product_uid);
    }

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

    public function getProductImages(): string
    {
        return $this->product_images;
    }

    public function getProductOldPrice(): int
    {
        return $this->product_old_price;
    }

    public function getProductCard(): ?string
    {
        return $this->product_card;
    }

    public function getArticle(): ?string
    {
        return $this->article;
    }

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

    public function getProductSize(): ?string
    {
        return $this->product_size;
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
        return $this->length;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function getProductProperty(): ?string
    {
        return $this->product_property;
    }

    public function getProductParams(): ?string
    {
        return $this->product_params;
    }

    public function getProductCurrency(): ?string
    {
        return $this->product_currency;
    }

    public function getProductQuantity(): ?int
    {
        return $this->product_quantity;
    }

    public function getMarketCategory(): ?int
    {
        return $this->market_category;
    }
    
    public function getSearchArticle(): ?string
    {
        $articles = json_decode($this->article, false, 512, JSON_THROW_ON_ERROR);

        if(false === empty($articles))
        {
            $article = $articles[0];

            // Находим позицию последнего дефиса
            $pos = strrpos($article, '-');

            if ($pos !== false) {
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
}