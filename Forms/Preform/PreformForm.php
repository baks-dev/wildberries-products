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

use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Wildberries\Products\Api\Settings\Category\FindAllWbCategoryRequest;
use BaksDev\Wildberries\Products\Api\Settings\Category\WbCategoryDTO;
use BaksDev\Wildberries\Products\Api\Settings\ParentCategory\FindAllWbParentCategoryRequest;
use BaksDev\Wildberries\Products\Api\Settings\ParentCategory\WbParentCategoryDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PreformForm extends AbstractType
{
    private UserProfileUid|false $profile;

    public function __construct(
        private readonly CategoryChoiceInterface $categoryChoice,
        private readonly FindAllWbParentCategoryRequest $wbParentCategory,
        private readonly FindAllWbCategoryRequest $wbCategoryRequest,
        private readonly UserProfileTokenStorageInterface $userProfileTokenStorage,
    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('id', ChoiceType::class, [
            'choices' => $this->categoryChoice->findAll(),
            'choice_value' => function(?CategoryProductUid $category) {
                return $category?->getValue();
            },
            'choice_label' => function(CategoryProductUid $category) {
                return (is_int($category->getAttr()) ? str_repeat(' - ', $category->getAttr() - 1) : '').$category->getOptions();
            },
            'label' => false,
            'required' => false,
        ]);


        $this->profile = $this->userProfileTokenStorage->getProfile();


        $parent = $this->wbParentCategory
            ->profile($this->profile)
            ->findAll();

        $builder
            ->add('parent', ChoiceType::class, [
                'choices' => $parent,
                'choice_value' => function(?WbParentCategoryDTO $type) {
                    return $type?->getId();
                },
                'choice_label' => function(WbParentCategoryDTO $type) {
                    return $type->getName();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);


        $builder->add('category', HiddenType::class);


        $formModifier = function(FormInterface $form, ?WbParentCategoryDTO $parent = null) {

            if($parent instanceof WbParentCategoryDTO)
            {

                $category = $this->wbCategoryRequest
                    ->profile($this->profile)
                    ->parent($parent->getId())
                    ->findAll();

                if(false === $category || false === $category->valid())
                {
                    return;
                }

                $form
                    ->add('category', ChoiceType::class, [
                        'choices' => $category,
                        'choice_value' => function(?WbCategoryDTO $category) {
                            return $category?->getId();
                        },
                        'choice_label' => function(WbCategoryDTO $category) {
                            return $category->getName();
                        },
                        'label' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'required' => true,
                        'placeholder' => 'Выберите категорию из списка...',
                    ]);

            }
        };


        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($formModifier) {

            /** @var PreformDTO $PreformDTO */
            $PreformDTO = $event->getData();

            false === ($PreformDTO->getParent() instanceof WbParentCategoryDTO) ?:
                $formModifier($event->getForm(), $PreformDTO->getParent());

        });

        $builder->get('parent')
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function(FormEvent $event) use ($formModifier) {
                    $data = $event->getForm()->getData();
                    $formModifier($event->getForm()->getParent(), $data);
                },
            );

        /* Сохранить ******************************************************/
        $builder->add(
            'preform',
            SubmitType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => PreformDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }

}
