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

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements;

use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\Type\Mapper\Products\AvitoBoardProductInterface;
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
 * Форма для отрисовки полей соответствия между категориями
 */
final class MapperElementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {

            $form = $event->getForm();

            /** @var MapperElementDTO $mapperElementDTO */
            if ($mapperElementDTO = $event->getData())
            {
                /** @var AvitoBoardProductInterface $avitoProduct */
                $avitoProduct = $options['avito_product'];
                $element = $avitoProduct->getElement($mapperElementDTO->getElement());
                $mapperElementDTO->setElementInstance($element);

                /** @var ArrayCollection<CategoryProductSectionFieldUid> $productFields */
                $productFields = $options['product_fields'];


                if (null === $element->getDefault())
                {
                    $form
                        ->add('productField', ChoiceType::class, [
                            'choices' => $productFields,
                            'choice_value' => function (?CategoryProductSectionFieldUid $field) {
                                return $field?->getValue();
                            },
                            'choice_label' => function (CategoryProductSectionFieldUid $field) {
                                return $field->getAttr();
                            },
                            'label' => $element->label(),
                            'help' => $element->getHelp(),
                            'expanded' => false,
                            'multiple' => false,
                            'required' => false,
                        ]);
                }

                if (false === $element->getDefault())
                {
                    if ($element->isChoices())
                    {
                        $form
                            ->add('def', ChoiceType::class, [
                                'choices' => $element->getDefault(),
                                'choice_value' => function (?string $element) {
                                    return $element;
                                },
                                'choice_label' => function (string $element) {
                                    return $element;
                                },
                                'label' => $element->label(),
                                'help' => $element->getHelp(),
                                'expanded' => false,
                                'multiple' => false,
                                'translation_domain' => 'avito-board.settings',
                                'required' => false,
                            ]);
                    }
                    else
                    {
                        $form->add('def', TextType::class, [
                            'data' => $mapperElementDTO->getDef() ?? $element->getDefault(),
                            'label' => $element->label(),
                            'help' => $element->getHelp(),
                            'translation_domain' => 'avito-board.settings',
                            'required' => false,
                        ]);
                    }
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MapperElementDTO::class,
                'product_fields' => null,
                'avito_product' => null,
            ]
        );
    }
}
