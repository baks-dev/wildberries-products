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

namespace BaksDev\Wildberries\Products\UseCase\Custom\NewEdit;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Wildberries\Products\Entity\Custom\Images\WildberriesProductCustomImage;
use BaksDev\Wildberries\Products\Entity\Custom\WildberriesProductCustom;
use BaksDev\Wildberries\Products\Messenger\Custom\WildberriesProductCustomMessage;
use BaksDev\Wildberries\Products\UseCase\Custom\NewEdit\Images\WildberriesProductCustomImagesDTO;

final class WildberriesCustomProductHandler extends AbstractHandler
{
    public function handle(WildberriesCustomProductDTO $command): string|WildberriesProductCustom
    {
        /** Добавляем command для валидации и гидрации */
        $this->setCommand($command);

        /** @var WildberriesProductCustom $entity */
        $entity = $this
            ->prePersistOrUpdate(
                WildberriesProductCustom::class,
                ['invariable' => $command->getInvariable()],
            );

        /**
         * Загружаем изображения
         *
         * @var WildberriesProductCustomImage $image
         */
        foreach($entity->getImages() as $image)
        {
            /** @var WildberriesProductCustomImagesDTO $WildberriesImagesDTO */
            if($WildberriesImagesDTO = $image->getEntityDto())
            {
                if(null !== $WildberriesImagesDTO->getFile())
                {
                    $this->imageUpload->upload($WildberriesImagesDTO->getFile(), $image);
                }
            }
        }

        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->flush();

        $this->messageDispatch->dispatch(
            message: new WildberriesProductCustomMessage($entity->getId()),
            transport: 'wildberries-products',
        );

        return $entity;
    }
}