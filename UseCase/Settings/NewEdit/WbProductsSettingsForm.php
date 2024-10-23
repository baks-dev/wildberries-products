<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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
    private PropertyFieldsCategoryChoiceInterface $propertyFields;

    private WbCharacteristicByObjectName $wbCharacteristic;

    private WbTokenByProfileInterface $wbTokenByProfile;


    public function __construct(
        PropertyFieldsCategoryChoiceInterface $propertyFields,
        WbCharacteristicByObjectName $wbCharacteristic,
        WbTokenByProfileInterface $wbTokenByProfile,
    )
    {
        $this->propertyFields = $propertyFields;
        $this->wbCharacteristic = $wbCharacteristic;
        $this->wbTokenByProfile = $wbTokenByProfile;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            /** @var WbProductsSettingsDTO $data */
            $data = $event->getData();

            $form = $event->getForm();

            /** Коллекция свойств категории для выпадающего списка */
            $property_fields = $this->propertyFields
                ->category($data->getSettings())
                ->getPropertyFieldsCollection();

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

            /* Category Trans */
            $form->add('property', CollectionType::class, [
                'entry_type' => Property\WbProductSettingsPropertyForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

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
