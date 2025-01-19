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

namespace BaksDev\Wildberries\Products\Repository\Barcode\AllBarcodeSettings;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Cover\CategoryProductCover;
use BaksDev\Products\Category\Entity\Event\CategoryProductEvent;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;

final class AllBarcodeSettingsRepository implements AllBarcodeSettingsInterface
{

    private ?SearchDTO $search = null;

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

    public function findPaginator(): PaginatorInterface
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $qb->select('barcode.event');

        $qb->from(WbBarcode::class, 'barcode');

        if($this->profile)
        {
            $qb
                ->where('barcode.profile = :profile')
                ->setParameter('profile', $this->profile, UserProfileUid::TYPE);
        }


        /** Категория */
        $qb->addSelect('category.id as category');
        $qb->addSelect('category.event as category_event'); /* ID события */
        $qb->join(
            'barcode',
            CategoryProduct::class,
            'category',
            'category.id = barcode.id'
        );


        /** События категории */
        $qb->addSelect('category_event.sort');

        $qb->join
        (
            'category',
            CategoryProductEvent::class,
            'category_event',
            'category_event.id = category.event'
        );


        /** Обложка */
        $qb->addSelect('category_cover.name AS cover');
        $qb->addSelect('category_cover.ext');
        $qb->addSelect('category_cover.cdn');
        $qb->leftJoin(
            'category_event',
            CategoryProductCover::class,
            'category_cover',
            'category_cover.event = category_event.id');


        /** Перевод категории */
        $qb->addSelect('category_trans.name as category_name');
        $qb->addSelect('category_trans.description as category_description');

        $qb->leftJoin(
            'category_event',
            CategoryProductTrans::class,
            'category_trans',
            'category_trans.event = category_event.id AND category_trans.local = :local');


        /* Поиск */
        if($this->search->getQuery())
        {
            $this->DBALQueryBuilder
                //  ->createSearchQueryBuilder($search)
                //
                //  ->addSearchEqualUid('product.id')
                //
                ->addSearchLike('category_trans.name');

        }

        return $this->paginator->fetchAllAssociative($qb);
    }

}