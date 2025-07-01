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

namespace BaksDev\Avito\Board\UseCase\NewEdit;

use BaksDev\Avito\Board\Mapper\AvitoBoardMapperProvider;
use BaksDev\Avito\Board\Mapper\Elements\AvitoBoardElementInterface;
use BaksDev\Avito\Board\UseCase\NewEdit\Elements\AvitoBoardMapperElementDTO;
use BaksDev\Avito\Board\UseCase\NewEdit\Elements\AvitoBoardMapperElementForm;
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
        private readonly AvitoBoardMapperProvider $mapperProvider,
        private readonly OffersCategoryProductSectionFieldInterface $offersCategoryProductSectionField,
        private readonly ModificationCategoryProductSectionFieldInterface $modificationCategoryProductSectionField,
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFields,
        private readonly VariationCategoryProductSectionFieldInterface $variationCategoryProductSectionField,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            $form = $event->getForm();

            /** @var AvitoBoardMapperDTO $avitoBoardMapperDTO */
            $avitoBoardMapperDTO = $event->getData();

            /**
             * Свойства, ТП, варианты, модификации продукта
             */
            $productFields = $this->getProductProperties($avitoBoardMapperDTO->getCategory());

            /**
             * Реализация продукта Авито
             */
            $avitoProduct = $this->mapperProvider->getProduct($avitoBoardMapperDTO->getAvito());

            /**
             * Фильтрация элементов (тегов) по категории - Авито
             * @var list<AvitoBoardElementInterface>|null $avitoElements
             */
            $avitoElements = $this->mapperProvider->filterElements($avitoBoardMapperDTO->getAvito());

            foreach($avitoElements as $element)
            {
                $mapperElementDTO = new AvitoBoardMapperElementDTO();
                $mapperElementDTO->setElement($element->element());

                $existFormElement = null;

                /** Проверка существования элемента в текущей форме */
                $existFormElement = $avitoBoardMapperDTO->getMapperElements()->findFirst(
                    function($key, $element) use ($mapperElementDTO) {

                        /** @var $element AvitoBoardMapperElementDTO */
                        return $element->getElement() === $mapperElementDTO->getElement();
                    });

                /** При добавлении нового элемента в форму */
                if(true === $element->isMapping() and null === $existFormElement)
                {
                    $avitoBoardMapperDTO->addMapperElement($mapperElementDTO);
                }

                /** При удалении уже добавленного элемента в форму */
                if(false === $element->isMapping() and $existFormElement instanceof AvitoBoardMapperElementDTO)
                {
                    $avitoBoardMapperDTO->removeMapperElement($existFormElement);
                }
            }

            $form->add('mapperElements', CollectionType::class, [
                'entry_type' => AvitoBoardMapperElementForm::class,
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
            'data_class' => AvitoBoardMapperDTO::class,
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

        /** Торговое предложение */
        $productOffer = $this->offersCategoryProductSectionField
            ->category($productCategory)
            ->findAllCategoryProductSectionField();

        /** Если нет свойств и ТП - отображаем пустой выпадающий список */
        if($productOffer === false && $productProperties === false)
        {
            $productFields->clear();

            return $productFields;
        }

        if($productOffer)
        {
            $productFields->add($productOffer);

            /** Вариант торгового предложения */
            $productVariation = $this->variationCategoryProductSectionField
                ->offer($productOffer->getValue())
                ->findAllCategoryProductSectionField();

            if($productVariation)
            {
                $productFields->add($productVariation);

                /** Модификация варианта торгового предложения */
                $productModification = $this->modificationCategoryProductSectionField
                    ->variation($productVariation->getValue())
                    ->findAllCategoryProductSectionField();

                if($productModification)
                {
                    $productFields->add($productModification);
                }
            }
        }

        return $productFields;
    }
}