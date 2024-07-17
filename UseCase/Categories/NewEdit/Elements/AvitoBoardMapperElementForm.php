<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Avito\Board\UseCase\Categories\NewEdit\Elements;

use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Форма для отрисовки полей соответствия между категориями -
 * передаются те поля, которые необходимо сопоставить
 */
final class AvitoBoardMapperElementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {

            /** @var AvitoBoardMapperElementDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            dump($data);

            if ($data)
            {
                /** @var ArrayCollection<CategoryProductSectionFieldUid> $localProperties */
                $localProperties = $options['property_fields'];


                $form
                    ->add('field', ChoiceType::class, [
                        'choices' => $localProperties,
                        'choice_value' => function (?CategoryProductSectionFieldUid $field) {
                            return $field?->getValue();
                        },
                        'choice_label' => function (CategoryProductSectionFieldUid $field) {
                            return $field->getAttr();
                        },
//                        'label' => $data->getTitle(),
//                        'help' => $data->getTitle().'_desc',
                        'expanded' => false,
                        'multiple' => false,
//                        'translation_domain' => 'yandex-market-products.property',
                        'required' => false,
                    ]);

                if ($choices = $data->getType()->choices())
                {
                    $form
                        ->add('def', ChoiceType::class, [
                            'choices' => $choices,
                            'choice_value' => function ($choice) {
                                return $choice;
                            },
                            'choice_label' => function ($choice) {
                                return $choice;
                            },
                            'expanded' => false,
                            'multiple' => false,
                            'translation_domain' => 'yandex-market-products.property',
//                            'data' => $data->getType()->getElement(),
                            'required' => $data->getType()->isRequired(),
                        ]);

                }
                else
                {
                    $form->add('def', TextType::class, [
                        'data' => $data->getType()->getElement(),
                        'required' => $data->getType()->isRequired(),
                        'disabled' => true,
                    ]);

                }

            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => AvitoBoardMapperElementDTO::class,
                'property_fields' => null,
            ]
        );
    }
}
