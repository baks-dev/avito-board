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
 *
 */

declare(strict_types=1);

namespace BaksDev\Avito\Board\Repository\AllProductsWithMapper;

use Symfony\Component\DependencyInjection\Attribute\Exclude;

/** @see AllProductsWithMapperRepository */
#[Exclude]
final readonly class AllProductsWithMapperResult
{
    public function __construct(
        private string $id,
        private string $event,
        private int $avito_kit_value,
        private string $avito_profile_percent,
        private string $avito_profile_address,
        private string $avito_profile_manager,
        private string $avito_profile_phone,
        private string $product_date_begin,
        private ?string $product_date_over,
        private string $product_name,
        private string $product_name_event,
        private string $product_description,
        private string|null $product_offer_id,
        private string|null $product_offer_const,
        private string|null $product_offer_value,
        private ?string $product_offer_postfix,
        private string $offer_section_field_uid,
        private string $product_offer_reference,
        private string|null $product_variation_id,
        private string|null $product_variation_const,
        private string|null $product_variation_value,
        private ?string $product_variation_postfix,
        private string|null $variation_section_field_uid,
        private string|null $product_modification_id,
        private string|null $product_modification_const,
        private string|null $product_modification_value,
        private ?string $product_modification_postfix,
        private string|null $modification_section_field_uid,
        private string $product_article,
        private bool $category_active,
        private string $product_category,
        private int|null $product_price,
        private int $product_old_price,
        private string|null $product_currency,
        private int $product_quantity,
        private string $product_images,
        private int|null $product_length_delivery,
        private int|null $product_width_delivery,
        private int|null $product_height_delivery,
        private int|null $product_weight_delivery,
        private string $avito_board_mapper_category_id,
        private string $avito_board_avito_category,
        private string $avito_board_mapper,
        private ?string $avito_product_description,
        private ?string $avito_product_images,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getAvitoKitValue(): int
    {
        return $this->avito_kit_value;
    }

    public function getAvitoProfilePercent(): string
    {
        return $this->avito_profile_percent;
    }

    public function getAvitoProfileAddress(): string
    {
        return $this->avito_profile_address;
    }

    public function getAvitoProfileManager(): string
    {
        return $this->avito_profile_manager;
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

    public function getProductNameEvent(): string
    {
        return $this->product_name_event;
    }

    public function getProductDescription(): string
    {
        return $this->product_description;
    }

    public function getProductOfferId(): ?string
    {
        return $this->product_offer_id;
    }

    public function getProductOfferConst(): ?string
    {
        return $this->product_offer_const;
    }

    public function getProductOfferValue(): ?string
    {
        return $this->product_offer_value;
    }

    public function getProductOfferPostfix(): ?string
    {
        return $this->product_offer_postfix;
    }

    public function getOfferSectionFieldUid(): string
    {
        return $this->offer_section_field_uid;
    }

    public function getProductOfferReference(): string
    {
        return $this->product_offer_reference;
    }

    public function getProductVariationId(): ?string
    {
        return $this->product_variation_id;
    }

    public function getProductVariationConst(): ?string
    {
        return $this->product_variation_const;
    }

    public function getProductVariationValue(): ?string
    {
        return $this->product_variation_value;
    }

    public function getProductVariationPostfix(): ?string
    {
        return $this->product_variation_postfix;
    }

    public function getVariationSectionFieldUid(): ?string
    {
        return $this->variation_section_field_uid;
    }

    public function getProductModificationId(): ?string
    {
        return $this->product_modification_id;
    }

    public function getProductModificationConst(): ?string
    {
        return $this->product_modification_const;
    }

    public function getProductModificationValue(): ?string
    {
        return $this->product_modification_value;
    }

    public function getProductModificationPostfix(): ?string
    {
        return $this->product_modification_postfix;
    }

    public function getModificationSectionFieldUid(): ?string
    {
        return $this->modification_section_field_uid;
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

    public function getProductPrice(): ?int
    {
        return $this->product_price;
    }

    public function getProductOldPrice(): int
    {
        return $this->product_old_price;
    }

    public function getProductCurrency(): ?string
    {
        return $this->product_currency;
    }

    public function getProductQuantity(): int
    {
        return $this->product_quantity;
    }

    public function getProductImages(): string
    {
        return $this->product_images;
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

    public function getAvitoBoardMapperCategoryId(): string
    {
        return $this->avito_board_mapper_category_id;
    }

    public function getAvitoBoardAvitoCategory(): string
    {
        return $this->avito_board_avito_category;
    }

    public function getAvitoBoardMapper(): string
    {
        return $this->avito_board_mapper;
    }

    public function getAvitoProductDescription(): ?string
    {
        return $this->avito_product_description;
    }

    public function getAvitoProductImages(): ?string
    {
        return $this->avito_product_images;
    }

}
