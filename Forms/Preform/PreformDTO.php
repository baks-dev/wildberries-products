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

namespace BaksDev\Wildberries\Products\Forms\Preform;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Wildberries\Products\Api\Settings\Category\WbCategoryDTO;
use BaksDev\Wildberries\Products\Api\Settings\ParentCategory\WbParentCategoryDTO;
use Symfony\Component\Validator\Constraints as Assert;

final class PreformDTO
{
    #[Assert\Uuid]
    #[Assert\NotBlank]
    public ?CategoryProductUid $id = null;

    /** Идентификаторы Wildberries */

    private ?WbParentCategoryDTO $parent = null;


    #[Assert\NotBlank]
    public ?WbCategoryDTO $category = null;

    /**
     * Id
     */
    public function getId(): ?CategoryProductUid
    {
        return $this->id;
    }

    public function setId(?CategoryProductUid $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Parent
     */
    public function getParent(): ?WbParentCategoryDTO
    {
        return $this->parent;
    }

    public function setParent(?WbParentCategoryDTO $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * Category
     */
    public function getCategory(): ?WbCategoryDTO
    {
        return $this->category;
    }

    public function setCategory(?WbCategoryDTO $category): self
    {
        $this->category = $category;
        return $this;
    }

}