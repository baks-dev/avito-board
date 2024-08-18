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

namespace BaksDev\Avito\Board\UseCase\BeforeNew;

use BaksDev\Avito\Board\Mapper\AvitoBoardMapperProvider;
use BaksDev\Avito\Board\Mapper\Products\AvitoBoardProductInterface;
use BaksDev\Avito\Board\Repository\AllCategoryWithMapper\AllCategoryWithMapperRepository;
use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvitoBoardCategoryMapperForm extends AbstractType
{
    public function __construct(
        private readonly AvitoBoardMapperProvider $mapperProvider,
        private readonly AllCategoryWithMapperRepository $allCategoryWithMapperRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** Получаем список локальных категории */
        $localCategory = $this->allCategoryWithMapperRepository->findAll();

        /** Получаем список наших категорий товаров */
        $builder
            ->add('localCategory', ChoiceType::class, [
                'choices' => $localCategory,
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

        /** Получаем список категории продуктов Avito */
        $avitoCategory = $this->mapperProvider->getProducts();

        $builder
            ->add('avitoCategory', ChoiceType::class, [
                'choices' => $avitoCategory,
                'choice_value' => static function (?AvitoBoardProductInterface $avitoCategories) {
                    return $avitoCategories?->getProductCategory();
                },
                'choice_label' => static function (AvitoBoardProductInterface $avitoCategories) {
                    return $avitoCategories;
                },
                'expanded' => false,
                'multiple' => false,
            ]);

        $builder->add(
            'mapper_before_new',
            SubmitType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => AvitoBoardCategoryMapperDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }
}
