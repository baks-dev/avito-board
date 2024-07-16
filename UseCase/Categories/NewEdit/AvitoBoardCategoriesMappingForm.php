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

namespace BaksDev\Avito\Board\UseCase\Categories\NewEdit;

use BaksDev\Avito\Board\Type\Categories\AvitoBoardCategoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvitoBoardCategoriesMappingForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $data = $event->getData();
            $form = $event->getForm();

            /** @var AvitoBoardCategoryInterface $element */
            $form->add('categories', CollectionType::class, [
                'entry_type' => AvitoBoardElementForm::class,
                'entry_options' => [
                    'label' => false,
                    'property_fields' => $data
                ],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

//            dd($data);
        });

        /* Сохранить ******************************************************/
        $builder->add(
            'categories_mapping',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvitoBoardCategoriesMappingDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ], );
    }
}
