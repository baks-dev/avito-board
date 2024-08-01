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

namespace BaksDev\Avito\Board\UseCase\Mapper\NewEdit;

use BaksDev\Avito\Board\Repository\Mapper\AllProductName\AllProductNameRepository;
use BaksDev\Avito\Board\Type\Mapper\AvitoBoardMapperProvider;
use BaksDev\Avito\Board\Type\Mapper\Elements\AvitoFeedElementInterface;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementDTO;
use BaksDev\Avito\Board\UseCase\Mapper\NewEdit\Elements\MapperElementForm;
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

final class MapperForm extends AbstractType
{
    public function __construct(
        private readonly AvitoBoardMapperProvider $mapperProvider,
        private readonly OffersCategoryProductSectionFieldInterface $offersCategoryProductSectionField,
        private readonly ModificationCategoryProductSectionFieldInterface $modificationCategoryProductSectionField,
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFields,
        private readonly VariationCategoryProductSectionFieldInterface $variationCategoryProductSectionField,
        private readonly AllProductNameRepository $test,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

            $form = $event->getForm();

            /** @var MapperDTO $mapperDTO */
            $mapperDTO = $event->getData();

            /**
             * Параметры продукта в системе (ТП, варианты, модификации)
             */
            $productFields = $this->getProductProperties($mapperDTO->getCategory());

            $avitoProduct = $this->mapperProvider->getProduct($mapperDTO->getAvito());

            /** Проверка для new */
            if ($mapperDTO->getMapperSetting()->isEmpty())
            {
                /**
                 * Фильтрация элементов по соответствующей категории Авито
                 * @var list<AvitoFeedElementInterface>|null $elements
                 */
                $elements = $this->mapperProvider->filterElements($mapperDTO->getAvito());

                foreach ($elements as $element)
                {
                    if ($element->isMapping())
                    {
                        $mapperElementDTO = new MapperElementDTO();
                        $mapperElementDTO->setElement($element->element());
                        $mapperDTO->addMapperSetting($mapperElementDTO);
                    }
                }
            }

            $form->add('mapperSetting', CollectionType::class, [
                'entry_type' => MapperElementForm::class,
                'entry_options' => [
                    'label' => false,
                    'product_fields' => $productFields,
                    'avito_product' => $avitoProduct,
                ],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

        });

        $builder->add('mapper_new', SubmitType::class, [
            'label' => 'Save',
            'label_html' => true,
            'attr' => ['class' => 'btn-primary']
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MapperDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }

    /**
     * @return ArrayCollection<CategoryProductSectionFieldUid>
     */
    private function getProductProperties(CategoryProductUid $productCategory): ArrayCollection
    {
        /**
         * Массив с элементами "свойства продукта"
         * @var list<CategoryProductSectionFieldUid> $productProperties
         */
        $productProperties = $this->propertyFields
            ->category($productCategory)
            ->getPropertyFieldsCollection();

        /** @var ArrayCollection<CategoryProductSectionFieldUid> $productFields */
        $productFields = new ArrayCollection($productProperties);


        //        $name = $this->test
//            ->category($productCategory)
//            ->findAll();
//
//        dump($name);

        /** Торговое предложение */
        $productOffer = $this->offersCategoryProductSectionField
            ->category($productCategory)
            ->findAllCategoryProductSectionField();

//        dd($productOffer);

        if ($productOffer)
        {
            $productFields->add($productOffer);

            /** Вариант торгового предложения */
            $productVariation = $this->variationCategoryProductSectionField
                ->offer($productOffer->getValue())
                ->findAllCategoryProductSectionField();

            if ($productVariation)
            {
                $productFields->add($productVariation);

                /** Модификация варианта торгового предложения */
                $productModification = $this->modificationCategoryProductSectionField
                    ->variation($productVariation->getValue())
                    ->findAllCategoryProductSectionField();

                if ($productModification)
                {
                    $productFields->add($productModification);
                }
            }
        }

        return $productFields;
    }
}
