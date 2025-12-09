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

namespace BaksDev\Wildberries\Products\Messenger\Cards\CardUpdate;

use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Invariable\ProductInvariableUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\ConstId\ProductModificationConst;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Type\id\WbTokenUid;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
final readonly class WildberriesCardUpdateMessage
{
    private string $profile;

    private string $product;

    private ?string $offerConst;

    private ?string $variationConst;

    private ?string $modificationConst;

    private string $invariable;


    public function __construct(
        UserProfileUid $profile,
        ProductUid $product,
        ProductOfferConst|false $offerConst,
        ProductVariationConst|false $variationConst,
        ProductModificationConst|false $modificationConst,
        ProductInvariableUid $invariable,
        private string $article,
    )
    {
        $this->profile = (string) $profile;
        $this->product = (string) $product;
        $this->offerConst = false === empty($offerConst) ? (string) $offerConst : null;
        $this->variationConst = false === empty($variationConst) ? (string) $variationConst : null;
        $this->modificationConst = false === empty($modificationConst) ? (string) $modificationConst : null;
        $this->invariable = (string) $invariable;
    }

    public function getProfile(): UserProfileUid
    {
        return new UserProfileUid($this->profile);
    }

    public function getProduct(): ProductUid
    {
        return new ProductUid($this->product);
    }

    public function getOfferConst(): ?ProductOfferConst
    {
        return null === $this->offerConst ? null : new ProductOfferConst($this->offerConst);
    }

    public function getVariationConst(): ?ProductVariationConst
    {
        return null === $this->variationConst ? null : new ProductVariationConst($this->variationConst);
    }

    public function getModificationConst(): ?ProductModificationConst
    {
        return null === $this->modificationConst ? null : new ProductModificationConst($this->modificationConst);
    }

    public function getArticle(): string
    {
        return $this->article;
    }

    public function getInvariable(): ProductInvariableUid
    {
        return new ProductInvariableUid($this->invariable);
    }
}
