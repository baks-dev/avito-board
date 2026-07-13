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

declare(strict_types=1);

namespace BaksDev\Avito\Board\Repository\AllProductsWithMapper;

use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Offers\Id\CategoryProductOffersUid;
use BaksDev\Products\Category\Type\Offers\Modification\CategoryProductModificationUid;
use BaksDev\Products\Category\Type\Offers\Variation\CategoryProductVariationUid;
use BaksDev\Products\Product\Repository\ProductPriceResultInterface;
use BaksDev\Products\Product\Type\Event\ProductEventUid;
use BaksDev\Products\Product\Type\Id\ProductUid;
use BaksDev\Products\Product\Type\Offers\ConstId\ProductOfferConst;
use BaksDev\Products\Product\Type\Offers\Id\ProductOfferUid;
use BaksDev\Products\Product\Type\Offers\Variation\ConstId\ProductVariationConst;
use BaksDev\Products\Product\Type\Offers\Variation\Id\ProductVariationUid;
use BaksDev\Products\Product\Type\Offers\Variation\Modification\Id\ProductModificationUid;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see AllProductsWithMapperRepository */
final class AllProductsWithMapperResult implements ProductPriceResultInterface
{

    private ?array $avito_board_mapper_decode = null;

    private ?array $product_property_decode = null;

    private ?array $avito_product_decode = null;

    private ?string $brand = null;

    private ?string $model = null;

    public function __construct(
        private readonly string $id,
        private readonly string $event,

        private readonly string $product_date_begin,
        private readonly ?string $product_date_over,
        private readonly string $product_name,
        private readonly ?string $product_description,
        private readonly ?string $product_offer_id,
        private readonly ?string $product_offer_const,
        private readonly ?string $product_offer_value,
        private readonly ?string $product_offer_postfix,
        private readonly ?string $offer_section_field_uid,
        private readonly string $product_offer_reference,
        private readonly ?string $product_variation_id,
        private readonly ?string $product_variation_const,
        private readonly ?string $product_variation_value,
        private readonly ?string $product_variation_postfix,
        private readonly ?string $variation_section_field_uid,
        private readonly ?string $product_modification_id,
        private readonly ?string $product_modification_const,
        private readonly ?string $product_modification_value,
        private readonly ?string $product_modification_postfix,
        private readonly ?string $modification_section_field_uid,
        private readonly string $product_article,
        private readonly bool $category_active,
        private readonly string $product_category,
        private readonly ?int $product_price,
        private readonly ?string $product_currency,
        private readonly ?string $product_quantity,
        private readonly ?string $product_images,
        private readonly ?int $product_length_delivery,
        private readonly ?int $product_width_delivery,
        private readonly ?int $product_height_delivery,
        private readonly ?int $product_weight_delivery,

        private readonly ?int $avito_kit_value,
        private readonly string $avito_profile_percent,
        private readonly ?string $avito_profile_address_id,
        private readonly ?string $avito_profile_address,
        private readonly string $avito_profile_manager,
        private readonly string $avito_profile_phone,
        private readonly string $avito_board_mapper_category_id,
        private readonly string $avito_board_avito_category,
        private readonly string $avito_board_mapper,

        private readonly ?string $avito_product,
        private readonly ?string $avito_product_images,

        private readonly string|null $project_discount = null,

        private ?bool $promotion_active = null,
        private string|null $promotion_price = null,

        private string|null $season_percent = null,
        private string|null $product_property = null,
    ) {}

    public function getProductId(): ProductUid
    {
        return new ProductUid($this->id);
    }

    public function getProductEvent(): ProductEventUid
    {
        return new ProductEventUid($this->event);
    }

    public function isAvitoProductSale(): bool
    {
        if(true === is_null($this->avito_product_decode))
        {
            if(empty($this->avito_product))
            {
                return false;
            }

            if(false === json_validate($this->avito_product))
            {
                return false;
            }

            /**
             * @var array{'value': string, 'element': string } $data
             */
            $data = json_decode($this->avito_product, true, 512, JSON_THROW_ON_ERROR);

            $current = current($data);

            if(empty($current))
            {
                return false;
            }

            $this->avito_product_decode = $current;
        }

        if(empty($this->avito_product_decode['avito_product_sale']))
        {
            return false;
        }

        return $this->avito_product_decode['avito_product_salse'];
    }


    public function getAvitoProductId(): string|null
    {
        if(true === is_null($this->avito_product_decode))
        {
            if(empty($this->avito_product))
            {
                return null;
            }

            if(false === json_validate($this->avito_product))
            {
                return null;
            }

            /**
             * @var array{'value': string, 'element': string } $data
             */
            $data = json_decode($this->avito_product, true, 512, JSON_THROW_ON_ERROR);

            $current = current($data);

            if(empty($current))
            {
                return null;
            }

            $this->avito_product_decode = $current;
        }

        if(empty($this->avito_product_decode['avito_product_id']))
        {
            return null;
        }

        return $this->avito_product_decode['avito_product_id'];
    }

    public function getAvitoProfilePhone(): string
    {
        return $this->avito_profile_phone;
    }

    public function getProductDateBegin(): string
    {
        return $this->product_date_begin;
    }

    public function getProductDateOver(): ?string
    {
        return $this->product_date_over;
    }

    public function getProductName(): string
    {
        return $this->product_name;
    }

    public function getProductDescription(): ?string
    {
        return $this->product_description;
    }

    public function getProductOfferId(): ?ProductOfferUid
    {
        return is_null($this->product_offer_id)
            ? null
            : new ProductOfferUid($this->product_offer_id);
    }

    public function getProductOfferConst(): ?ProductOfferConst
    {
        return is_null($this->product_offer_const)
            ? null
            : new ProductOfferConst($this->product_offer_const);
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getOfferSectionFieldUid(): ?CategoryProductOffersUid
    {
        return is_null($this->offer_section_field_uid)
            ? null
            : new CategoryProductOffersUid($this->offer_section_field_uid);
    }

    public function getProductOfferReference(): string
    {
        return $this->product_offer_reference;
    }

    public function getProductVariationId(): ?ProductVariationUid
    {
        return is_null($this->product_variation_id)
            ? null
            : new ProductVariationUid($this->product_variation_id);
    }

    public function getProductVariationConst(): ?ProductVariationConst
    {
        return is_null($this->product_variation_const)
            ? null
            : new ProductVariationConst($this->product_variation_const);
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getVariationSectionFieldUid(): ?CategoryProductVariationUid
    {
        return is_null($this->variation_section_field_uid)
            ? null
            : new CategoryProductVariationUid($this->variation_section_field_uid);
    }

    public function getProductModificationId(): ?string
    {
        return $this->product_modification_id;
    }

    public function getProductModificationConst(): ?ProductModificationUid
    {
        return is_null($this->product_modification_const)
            ? null
            : new ProductModificationUid($this->product_modification_const);
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getModificationSectionFieldUid(): ?CategoryProductModificationUid
    {
        return is_null($this->modification_section_field_uid)
            ? null
            : new CategoryProductModificationUid($this->modification_section_field_uid);
    }

    public function getProductArticle(): string
    {
        return $this->product_article;
    }

    public function isCategoryActive(): bool
    {
        return $this->category_active;
    }

    public function getProductCategory(): string
    {
        return $this->product_category;
    }

    public function getProductPrice(): Money|false
    {

        if(empty($this->product_price))
        {
            return false;
        }

        $price = new Money($this->product_price, true);

        /* Торговая наценка с учетом сезонности */
        if(false === empty($this->season_percent))
        {
            $price->applyString($this->season_percent);
        }

        /** Акция/наценка магазина (promotion) */
        if(false === empty($this->promotion_price) && true === $this->promotion_active)
        {
            $price->applyString($this->promotion_price);
        }

        /** Торговая наценка/скидка профиля магазина */
        if(false === empty($this->project_discount))
        {
            $price->applyString($this->project_discount);
        }

        /** Наценка/скидка токена профиля магазина */
        if(false === empty($this->avito_profile_percent))
        {
            $price->applyString($this->avito_profile_percent);
        }

        return $price;
    }

    public function getProductCurrency(): Currency
    {
        return new Currency($this->product_currency);
    }

    public function getProductQuantity(): int
    {

        if(empty($this->product_quantity))
        {
            return 0;
        }

        if(false === json_validate($this->product_quantity))
        {
            return 0;
        }

        $decode = json_decode($this->product_quantity, false, 512, JSON_THROW_ON_ERROR);

        $quantity = 0;

        foreach($decode as $item)
        {
            $quantity += $item->total;
            $quantity -= $item->reserve;
        }

        return max($quantity, 0);
    }

    public function getProductImages(): array|null
    {
        if(is_null($this->product_images))
        {
            return null;
        }

        if(false === json_validate($this->product_images))
        {
            return null;
        }

        $images = json_decode($this->product_images, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getProductLengthDelivery(): ?int
    {
        return $this->product_length_delivery;
    }

    public function getProductWidthDelivery(): ?int
    {
        return $this->product_width_delivery;
    }

    public function getProductHeightDelivery(): ?int
    {
        return $this->product_height_delivery;
    }

    public function getProductWeightDelivery(): ?int
    {
        return $this->product_weight_delivery;
    }

    public function getAvitoBoardMapperCategoryId(): CategoryProductUid
    {
        return new CategoryProductUid($this->avito_board_mapper_category_id);
    }

    public function getAvitoBoardAvitoCategory(): string
    {
        return $this->avito_board_avito_category;
    }

    public function getAvitoProductDescription(): ?string
    {
        if(true === is_null($this->avito_product_decode))
        {
            if(empty($this->avito_product))
            {
                return null;
            }

            if(false === json_validate($this->avito_product))
            {
                return null;
            }

            /**
             * @var array{'value': string, 'element': string } $data
             */
            $data = json_decode($this->avito_product, true, 512, JSON_THROW_ON_ERROR);

            $current = current($data);

            if(empty($current))
            {
                return null;
            }

            $this->avito_product_decode = $current;
        }

        if(empty($this->avito_product_decode['avito_product_description']))
        {
            return null;
        }

        return $this->avito_product_decode['avito_product_description'];
    }

    public function getAvitoProductImages(): array|null
    {
        if(is_null($this->avito_product_images))
        {
            return null;
        }

        if(false === json_validate($this->avito_product_images))
        {
            return null;
        }

        $images = json_decode($this->avito_product_images, false, 512, JSON_THROW_ON_ERROR);

        if(null === current($images))
        {
            return null;
        }

        return $images;
    }

    public function getAvitoKitValue(): ?int
    {
        return $this->avito_kit_value ?? 0;
    }

    public function getAvitoProfilePercent(): string
    {
        return $this->avito_profile_percent;
    }

    public function getAvitoProfileAddress(): ?string
    {
        return $this->avito_profile_address;
    }

    public function getAvitoSellerAddressId(): ?string
    {
        return $this->avito_profile_address_id;
    }


    public function getAvitoProfileManager(): string
    {
        return $this->avito_profile_manager;
    }

    /** Property Avito Board Mapper */
    public function getAvitoBoardPropertyMapper(): ?array
    {
        if(true === is_null($this->avito_board_mapper_decode))
        {
            if(is_null($this->avito_board_mapper))
            {
                return null;
            }

            if(false === json_validate($this->avito_board_mapper))
            {
                return null;
            }

            /**
             * @var array{'value': string, 'element': string } $data
             */
            $data = json_decode($this->avito_board_mapper, false, 512, JSON_THROW_ON_ERROR);

            if(null === current($data))
            {
                return null;
            }

            $propertyMap = $this->getProductPropertyMapper();

            $elementMap = [];

            // Обновляем значения элементов
            foreach($data as $element)
            {
                if(isset($element->field) && isset($propertyMap[$element->field]))
                {
                    $element->value = $propertyMap[$element->field]->value;
                    $elementMap[$element->element] = $element;
                    continue;
                }

                /** Поиск по торговому предложению */
                if($this->getOfferSectionFieldUid()->equals($element->field))
                {
                    $element->value = $this->getProductOfferValue();
                    $elementMap[$element->element] = $element;
                    continue;
                }

                /** Поиск по множественному варианту */
                if($this->getVariationSectionFieldUid()->equals($element->field))
                {
                    $element->value = $this->getProductVariationValue();
                    $elementMap[$element->element] = $element;
                    continue;
                }

                /** Поиск по модификации множественного варианта */
                if($this->getModificationSectionFieldUid()->equals($element->field))
                {
                    $element->value = $this->getProductModificationValue();
                    $elementMap[$element->element] = $element;
                    continue;
                }

                /** Если имеется значение по умолчанию - присваиваем */
                if(isset($element->default))
                {
                    $element->value = $element->default;
                    $elementMap[$element->element] = $element;
                    continue;
                }

                $element->value = null;
                $elementMap[$element->element] = $element;

            }

            unset($element, $data);

            $this->avito_board_mapper_decode = $elementMap;
        }

        return $this->avito_board_mapper_decode;
    }

    public function getProductOldPrice(): Money|false
    {
        return false;
    }

    /** Property Avito Board Mapper */
    public function getProductPropertyMapper(): ?array
    {
        if(true === is_null($this->product_property_decode))
        {
            if(is_null($this->product_property))
            {
                return null;
            }

            if(false === json_validate($this->product_property))
            {
                return null;
            }

            /**
             * @var array{'value': string, 'element': string } $data
             */
            $data = json_decode($this->product_property, false, 512, JSON_THROW_ON_ERROR);

            if(null === current($data))
            {
                return null;
            }

            $propertyMap = [];

            foreach($data as $prop)
            {
                if(isset($prop->field))
                {
                    $propertyMap[$prop->field] = $prop;
                }
            }

            $this->product_property_decode = $propertyMap;
        }

        return $this->product_property_decode;
    }

    public function getProductBrand(): ?string
    {
        return $this->brand;
    }

    public function setProductBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getProductModel(): ?string
    {
        return $this->model;
    }

    public function setProductModel(?string $model): self
    {
        $this->model = $model;
        return $this;
    }

}