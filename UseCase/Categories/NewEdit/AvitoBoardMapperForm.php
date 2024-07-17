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

use BaksDev\Avito\Board\Type\Categories\AvitoBoardFeedElementInterface;
use BaksDev\Avito\Board\Type\Categories\AvitoBoardCategoryProvider;
use BaksDev\Avito\Board\UseCase\Categories\NewEdit\Elements\AvitoBoardMapperElementDTO;
use BaksDev\Avito\Board\UseCase\Categories\NewEdit\Elements\AvitoBoardMapperElementForm;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\ModificationCategoryProductSectionField\ModificationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\OffersCategoryProductSectionField\OffersCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\VariationCategoryProductSectionField\VariationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AvitoBoardMapperForm extends AbstractType
{
    public function __construct(
        private readonly OffersCategoryProductSectionFieldInterface       $offersCategoryProductSectionField,
        private readonly VariationCategoryProductSectionFieldInterface    $variationCategoryProductSectionField,
        private readonly ModificationCategoryProductSectionFieldInterface $modificationCategoryProductSectionField,
        private readonly PropertyFieldsCategoryChoiceInterface            $propertyFields,
        private readonly AvitoBoardCategoryProvider                       $categoryProvider,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $form = $event->getForm();

            /** @var AvitoBoardMapperDTO $mapperDTO */
            $mapperDTO = $event->getData();

            /**
             * Параметры продукта в системе (ТПб варианты, модификации)
             */
            $propertyFields = $this->getProductParameters($mapperDTO->getLocalCategory());

            /**
             * Массив теггированных элементов для соответствующей категории Авито
             * @var list<AvitoBoardFeedElementInterface>|null $elements
             */
            $elements = $this->categoryProvider->getFeedElements($mapperDTO->getAvitoCategory());

            foreach ($elements as $element)
            {
                dump($element->choices());
                $elementDTO = new AvitoBoardMapperElementDTO();
                $elementDTO->setFeedElement($element);
                $mapperDTO->addCategory($elementDTO);
            }
            dd();

            $form->add('categories', CollectionType::class, [
                'entry_type' => AvitoBoardMapperElementForm::class,
                'entry_options' => [
                    'label' => false,
                    'property_fields' => $propertyFields
                ],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);
        });

        $builder->add('categories_mapping', SubmitType::class, [
            'label' => 'Save',
            'label_html' => true,
            'attr' => ['class' => 'btn-primary']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AvitoBoardMapperDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }

    /**
     * @return ArrayCollection<CategoryProductSectionFieldUid>
     */
    private function getProductParameters(CategoryProductUid $productCategory): ArrayCollection
    {
        /**
         * Массив с элементами "свойства продукта"
         * @var list<CategoryProductSectionFieldUid> $productProperties
         */
        $productProperties = $this->propertyFields
            ->category($productCategory)
            ->getPropertyFieldsCollection();

        /** @var ArrayCollection<CategoryProductSectionFieldUid> $propertyFields */
        $propertyFields = new ArrayCollection($productProperties);

        /** Торговое предложение */
        $productOffer = $this->offersCategoryProductSectionField
            ->category($productCategory)
            ->findAllCategoryProductSectionField();

        if ($productOffer)
        {
            $propertyFields->add($productOffer);

            /** Вариант торгового предложения */
            $productVariation = $this->variationCategoryProductSectionField
                ->offer($productOffer->getValue())
                ->findAllCategoryProductSectionField();

            if ($productVariation)
            {
                $propertyFields->add($productVariation);

                /** Модификация варианта торгового предложения */
                $productModification = $this->modificationCategoryProductSectionField
                    ->variation($productVariation->getValue())
                    ->findAllCategoryProductSectionField();

                if ($productModification)
                {
                    $propertyFields->add($productModification);
                }
            }
        }

        return $propertyFields;
    }
}
