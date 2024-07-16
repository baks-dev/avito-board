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

namespace BaksDev\Avito\Board\UseCase\Categories\BeforeNew;

use BaksDev\Avito\Board\Type\Categories\AvitoBoardCategoryInterface;
use BaksDev\Avito\Board\Type\Categories\AvitoBoardCategoryProvider;
use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MappingCategoryForm extends AbstractType
{
    public function __construct(
        private AvitoBoardCategoryProvider $categoryProvider,
        private CategoryChoiceInterface $categoryChoice,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Получаем список наших категорий товаров */
        $builder
            ->add('localCategory', ChoiceType::class, [
                'choices' => $this->categoryChoice->findAll(),
                'choice_value' => function (?CategoryProductUid $type) {
                    return $type?->getValue();
                },
                'choice_label' => function (CategoryProductUid $type) {
                    return $type?->getOptions();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);

        /**
         * Получаем список категории Avito
         */
        $avitoCategories = $this->categoryProvider->getCategories();

        $builder
            ->add('avitoCategory', ChoiceType::class, [
                'choices' => $avitoCategories,
                'choice_value' => static function (?AvitoBoardCategoryInterface $avitoCategories) {
                    return $avitoCategories?->getCategory();
                },
                'choice_label' => static function (AvitoBoardCategoryInterface $avitoCategories) {
                    return $avitoCategories->getCategory() . ' / ' . $avitoCategories->getTitle();
                },
                'expanded' => false,
                'multiple' => false,
            ]);

        /** Сохранить */
        $builder->add(
            'avito_board_before_new',
            SubmitType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => MappingCategoryDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }

}
