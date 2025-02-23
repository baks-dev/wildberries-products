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

namespace BaksDev\Wildberries\Products\Repository\Barcode\WbBarcodeSettings;

final class WbBarcodeSettingsResult
{
    private bool $offer;

    private bool $variation;

    private bool $modification;

    private int $counter;

    private array $property;

    private array $custom;

    public function __construct(...$data)
    {

        $this->offer = (bool) $data['offer'];
        $this->variation = (bool) $data['variation'];
        $this->modification = (bool) $data['modification'];

        $this->counter = $data['counter'] ?: 1;

        $this->property = $data['property'] ? json_decode($data['property'], false, 512, JSON_THROW_ON_ERROR) : [];
        $this->custom = $data['custom'] ? json_decode($data['custom'], false, 512, JSON_THROW_ON_ERROR) : [];
    }

    /**
     * Offer
     */
    public function isOffer(): bool
    {
        return $this->offer;
    }

    /**
     * Variation
     */
    public function isVariation(): bool
    {
        return $this->variation;
    }

    /**
     * Modification
     */
    public function isModification(): bool
    {
        return $this->modification;
    }

    /**
     * Counter
     */
    public function getCounter(): int
    {
        return empty($this->counter) ? 1 : $this->counter;
    }

    /**
     * Property
     */
    public function getProperty(): array
    {
        return $this->property ?: [];
    }

    /**
     * Custom
     */
    public function getCustom(): array
    {
        return $this->custom ?: [];
    }

}