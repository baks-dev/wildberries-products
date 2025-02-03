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

namespace BaksDev\Wildberries\Products\UseCase\Settings\NewEdit;

use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Wildberries\Api\Token\Reference\Characteristics\WbCharacteristicByObjectName;
use BaksDev\Wildberries\Api\Token\Reference\Characteristics\WbCharacteristicByObjectNameDTO;
use BaksDev\Wildberries\Products\Mapper\Params\WildberriesProductParametersCollection;
use BaksDev\Wildberries\Products\Mapper\Params\WildberriesProductParametersInterface;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyCollection;
use BaksDev\Wildberries\Products\Mapper\Property\WildberriesProductPropertyInterface;
use BaksDev\Wildberries\Products\Type\Settings\Property\WildberriesProductProperty;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Parameters\WbProductSettingsParametersDTO;
use BaksDev\Wildberries\Products\UseCase\Settings\NewEdit\Property\WbProductSettingsPropertyDTO;
use BaksDev\Wildberries\Repository\WbTokenByProfile\WbTokenByProfileInterface;
use DomainException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class WbProductsSettingsForm extends AbstractType
{
    public function __construct(
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFields,
        private readonly WbCharacteristicByObjectName $wbCharacteristic,
        private readonly WbTokenByProfileInterface $wbTokenByProfile,

        private readonly WildberriesProductPropertyCollection $wildberriesProductPropertyCollection,
        private readonly WildberriesProductParametersCollection $wildberriesProductParamsCollection

    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            /** @var WbProductsSettingsDTO $data */
            $data = $event->getData();

            $form = $event->getForm();


            /** Коллекция свойств категории для выпадающего списка */
            $property_fields = $this->propertyFields
                ->category($data->getMain())
                ->getPropertyFieldsCollection();

            /* Category Trans */
            $form->add('property', CollectionType::class, [
                'entry_type' => Property\WbProductSettingsPropertyForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);


            $property = $this->wildberriesProductPropertyCollection->cases($data->getCategory());

            $property = array_filter($property, static function(WildberriesProductPropertyInterface $element) {
                return $element->isSetting() === true;
            });

            /** @var WildberriesProductPropertyInterface $prop */
            foreach($property as $prop)
            {
                $filter = $data->getProperty()->filter(function(WbProductSettingsPropertyDTO $element) use ($prop) {
                    return $element->getType()->equals($prop->getIndex());
                });

                if($filter->isEmpty())
                {
                    $WbProductSettingsPropertyDTO = new WbProductSettingsPropertyDTO()
                        ->setType(new WildberriesProductProperty($prop->getIndex()))
                        //->setName($prop->getName())
                        ->setRequired($prop->required());

                    $data->addProperty($WbProductSettingsPropertyDTO);

                    continue;
                }

                $WbProductSettingsPropertyDTO = $filter->current();
                $WbProductSettingsPropertyDTO->setRequired($prop->required());
            }


            /** Характеристики товара */


            $characteristics = $this->wildberriesProductParamsCollection->cases($data->getCategory());

            $characteristics = array_filter($characteristics, static function(
                WildberriesProductParametersInterface $element
            ) {
                return $element->isSetting() === true;
            });

            /** @var WildberriesProductParametersInterface $characteristic */
            foreach($characteristics as $characteristic)
            {
                $filter = $data->getParameter()->filter(function(WbProductSettingsParametersDTO $element) use (
                    $characteristic
                ) {
                    return $element->getType() === (string) $characteristic::ID;
                });

                if($filter->isEmpty())
                {
                    $WbProductSettingsParametersDTO = new WbProductSettingsParametersDTO()
                        ->setType($characteristic::ID)
                        ->setName($characteristic->getName())
                        ->setRequired($characteristic->required());


                    $data->addParameter($WbProductSettingsParametersDTO);

                    continue;
                }

                $WbProductSettingsParametersDTO = $filter->current();
                $WbProductSettingsParametersDTO->setRequired($characteristic->required());
                //$WbProductSettingsParametersDTO->setName($characteristic->getName());
            }


            /* Category Trans */
            $form->add('parameter', CollectionType::class, [
                'entry_type' => Parameters\WbProductSettingsParametersForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);


            return;


            $profile = $this->wbTokenByProfile->getCurrentUserProfile();

            $characteristics = [];


            if($profile)
            {
                try
                {
                    $characteristics = $this->wbCharacteristic
                        ->profile($profile)
                        ->name($data->getName())
                        ->findCharacteristics();


                }
                catch(DomainException $e)
                {
                    /** Если токен авторизации не найден */
                    $characteristics = [];
                }
            }

            /** @var WbCharacteristicByObjectNameDTO $characteristic */
            foreach($characteristics as $characteristic)
            {

                if($characteristic->getName() === 'Наименование' || $characteristic->getName() === 'Описание')
                {
                    continue;
                }

                $new = true;


                /** @var WbProductSettingsPropertyDTO $property */
                foreach($data->getProperty() as $property)
                {
                    if($property->getType() === $characteristic->getName())
                    {
                        $property->setRequired($characteristic->isRequired());
                        $property->setUnit($characteristic->getUnit());
                        $property->setPopular($characteristic->isPopular());

                        $new = false;
                        break;
                    }
                }

                if($new)
                {
                    /**
                     * "objectName": "Косухи", - Наименование подкатегории
                     * "name": "Особенности модели", - Наименование характеристики
                     * "required": false, - Характеристика обязательна к заполнению
                     * "unitName": "", - Единица измерения (см, гр и т.д.)
                     * "maxCount": 1, - Максимальное кол-во значений которое можно присвоить данной характеристике
                     * "popular": false, - Характеристика популярна у пользователей
                     * "charcType": 1 - Тип характеристики (1 и 0 - строка или массив строк; 4 - число или массив чисел)
                     */

                    $WbProductSettingsPropertyDTO = new WbProductSettingsPropertyDTO();
                    $WbProductSettingsPropertyDTO->setType($characteristic->getName());

                    $WbProductSettingsPropertyDTO->setRequired($characteristic->isRequired());
                    $WbProductSettingsPropertyDTO->setUnit($characteristic->getUnit());
                    $WbProductSettingsPropertyDTO->setPopular($characteristic->isPopular());

                    $data->addProperty($WbProductSettingsPropertyDTO);
                }

            }


        });


        /* Сохранить ******************************************************/
        $builder->add(
            'product_settings',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WbProductsSettingsDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ],);
    }

}
