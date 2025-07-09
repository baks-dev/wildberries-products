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

namespace BaksDev\Wildberries\Products\Mapper\Property\Collection;

use BaksDev\Wildberries\Products\Mapper\Params\WildberriesProductParametersCollection;
use BaksDev\Wildberries\Products\Mapper\Params\WildberriesProductParametersInterface;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Repository\Cards\CurrentWildberriesProductsCard\WildberriesProductsCardResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AutoconfigureTag('baks.wb.product.property')]
final class CharacteristicsWildberriesProductProperty implements WildberriesProductPropertyInterface
{
    /**
     * Значение характеристики.
     */
    public const string PARAM = 'characteristics';

    public function __construct(
        private readonly ?WildberriesProductParametersCollection $paramsCollection = null,
        private readonly ?TranslatorInterface $translator = null,
    ) {}

    public function getIndex(): string
    {
        return self::PARAM;
    }

    public function required(): bool
    {
        return true;
    }

    public function default(): ?string
    {
        return null;
    }

    /** Массив допустимых значений */
    public function choices(): ?array
    {
        return null;
    }

    /**
     * Сортировка (чем меньше число - тем первым в итерации будет значение)
     */
    public static function priority(): int
    {
        return 200;
    }

    /**
     * Проверяет, относится ли значение к данному объекту
     */
    public static function equals(string $param): bool
    {
        return self::PARAM === $param;
    }

    public function isSetting(): bool
    {
        return false;
    }


    public function getData(WildberriesProductsCardResult $data): ?array
    {

        if(true === empty($data->getMarketCategory()))
        {
            return null;
        }

        /** Получаем параметры категории */
        $cases = $this->paramsCollection->cases($data->getMarketCategory());

        /** @var WildberriesProductParametersInterface $param */
        foreach($cases as $param)
        {
            $parameter = $param->getData($data, $this->translator);

            if(
                is_array($parameter) &&
                !empty($parameter['id']) &&
                isset($parameter['value'])
            )
            {
                $params[] = $parameter;
            }
        }


        return $params ?? null;
    }
}
