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

namespace BaksDev\Wildberries\Products\Repository\Cards\AllWbProductCard;


use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Product\Forms\ProductFilter\ProductFilterInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Entity\Cards\WbProductCard;
use BaksDev\Wildberries\Products\Entity\Cards\WbProductCardOffer;
use BaksDev\Wildberries\Products\Entity\Cards\WbProductCardVariation;

final class AllWbProductCardRepository implements AllWbProductCardInterface
{

    private ?SearchDTO $search = null;

    private ?ProductFilterInterface $filter = null;

    private UserProfileUid|false $profile = false;


    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly PaginatorInterface $paginator,
    ) {}


    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function filter(ProductFilterInterface $filter): self
    {
        $this->filter = $filter;
        return $this;
    }

    /**
     * Profile
     */
    public function profile(UserProfile|UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /** Метод возвращает пагинатор WbProductCard */
    public function findPaginator(): PaginatorInterface
    {

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->select('product.id AS product_id');
        $dbal->addSelect('product.event AS product_event');

        $dbal->from(Product::class, 'product');

        $dbal
            ->addSelect('card.id AS card_id')
            ->join(
                'product',
                WbProductCard::class,
                'card',
                'card.product = product.id'
            );


        $dbal->addSelect('product_trans.name AS product_name');
        //$dbal->addSelect('product_trans.preview AS product_preview');
        $dbal->leftJoin(
            'product',
            ProductTrans::class,
            'product_trans',
            'product_trans.event = product.event AND product_trans.local = :local'
        );

        if($this->profile)
        {
            $dbal->andWhere('product_info.profile = :profile');
            $dbal->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }

        /* ProductInfo */

        $dbal->addSelect('product_info.url');

        $dbal->leftJoin(
            'product',
            ProductInfo::class,
            'product_info',
            'product_info.product = product.id'
        );


        /** Ответственное лицо (Профиль пользователя) */

        $dbal->leftJoin(
            'product_info',
            UserProfile::class,
            'users_profile',
            'users_profile.id = product_info.profile'
        );

        $dbal->addSelect('users_profile_personal.username AS users_profile_username');
        $dbal->leftJoin(
            'users_profile',
            UserProfilePersonal::class,
            'users_profile_personal',
            'users_profile_personal.event = users_profile.event'
        );


        /** Торговое предложение */

        $dbal->addSelect('product_offer.id as product_offer_id');
        $dbal->addSelect('product_offer.value as product_offer_value');
        $dbal->addSelect('product_offer.postfix as product_offer_postfix');

        $dbal->leftJoin(
            'product',
            ProductOffer::class,
            'product_offer',
            'product_offer.event = product.event'
        );


        $dbal->addSelect('card_offer.nomenclature');
        $dbal->join(
            'card',
            WbProductCardOffer::class,
            'card_offer',
            'card_offer.card = card.id AND card_offer.offer = product_offer.const'
        );


        if($this->filter->getOffer())
        {
            $dbal->andWhere('product_offer.value = :offer');
            $dbal->setParameter('offer', $this->filter->getOffer());
        }


        /* Тип торгового предложения */
        $dbal->addSelect('category_offer.reference as product_offer_reference');
        $dbal->leftJoin(
            'product_offer',
            CategoryProductOffers::class,
            'category_offer',
            'category_offer.id = product_offer.category_offer'
        );


        /** Множественные варианты торгового предложения */

        $dbal->addSelect('product_variation.id as product_variation_id');
        $dbal->addSelect('product_variation.value as product_variation_value');
        $dbal->addSelect('product_variation.postfix as product_variation_postfix');

        $dbal->leftJoin(
            'product_offer',
            ProductVariation::class,
            'product_variation',
            'product_variation.offer = product_offer.id'
        );


        if($this->filter->getVariation())
        {
            $dbal->andWhere('product_variation.value = :variation');
            $dbal->setParameter('variation', $this->filter->getVariation());
        }


        /* Тип множественного варианта торгового предложения */
        $dbal->addSelect('category_offer_variation.reference as product_variation_reference');
        $dbal->leftJoin(
            'product_variation',
            CategoryProductVariation::class,
            'category_offer_variation',
            'category_offer_variation.id = product_variation.category_variation'
        );


        $dbal->addSelect('card_variation.barcode');
        $dbal->leftJoin(
            'product_variation',
            WbProductCardVariation::class,
            'card_variation',
            'card_variation.card = card.id AND card_variation.variation = product_variation.const'
        );


        /** Артикул продукта */

        $dbal->addSelect("
					CASE
					   WHEN product_variation.article IS NOT NULL THEN product_variation.article
					   WHEN product_offer.article IS NOT NULL THEN product_offer.article
					   WHEN product_info.article IS NOT NULL THEN product_info.article
					   ELSE NULL
					END AS product_article
				"
        );


        /** Фото продукта */

        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event AND product_photo.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id AND product_variation_image.root = true'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id AND product_offer_images.root = true'
        );

        $dbal->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name)
			   WHEN product_offer_images.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name)
			   WHEN product_photo.name IS NOT NULL THEN
					CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name)
			   ELSE NULL
			END AS product_image
		"
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					product_variation_image.ext
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.ext
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.ext
			   ELSE NULL
			END AS product_image_ext
		");

        /** Флаг загрузки файла CDN */
        $dbal->addSelect("
			CASE
			   WHEN product_variation_image.name IS NOT NULL THEN
					product_variation_image.cdn
			   WHEN product_offer_images.name IS NOT NULL THEN
					product_offer_images.cdn
			   WHEN product_photo.name IS NOT NULL THEN
					product_photo.cdn
			   ELSE NULL
			END AS product_image_cdn
		");


        /* Категория */
        $dbal->join(
            'product',
            ProductCategory::class,
            'product_event_category',
            'product_event_category.event = product.event AND product_event_category.root = true'
        );

        if($this->filter->getCategory())
        {
            $dbal->andWhere('product_event_category.category = :category');
            $dbal->setParameter('category', $this->filter->getCategory(), CategoryProductUid::TYPE);
        }

        $dbal->join(
            'product_event_category',
            CategoryProduct::class,
            'category',
            'category.id = product_event_category.category'
        );

        $dbal->addSelect('category_trans.name AS category_name');

        $dbal->leftJoin(
            'category',
            CategoryProductTrans::class,
            'category_trans',
            'category_trans.event = category.event AND category_trans.local = :local'
        );


        if($this->search->getQuery())
        {

            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchEqualUid('product.id')
                ->addSearchEqualUid('product.event')
                ->addSearchEqualUid('product_variation.id')
                ->addSearchLike('product_trans.name')
                //->addSearchLike('product_trans.preview')
                ->addSearchLike('product_info.article')
                ->addSearchLike('product_offer.article')
                ->addSearchLike('product_variation.article');

        }

        $dbal->orderBy('product.event', 'DESC');

        return $this->paginator->fetchAllAssociative($dbal);
    }


}
