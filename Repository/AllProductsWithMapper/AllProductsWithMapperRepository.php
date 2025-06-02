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

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Element\AvitoBoardMapperElement;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\Address\AvitoTokenAddress;
use BaksDev\Avito\Entity\Event\AvitoTokenEvent;
use BaksDev\Avito\Entity\Event\Manager\AvitoTokenManager;
use BaksDev\Avito\Entity\Event\Percent\AvitoTokenPercent;
use BaksDev\Avito\Entity\Event\Phone\AvitoTokenPhone;
use BaksDev\Avito\Entity\Profile\AvitoTokenProfile;
use BaksDev\Avito\Products\Entity\AvitoProduct;
use BaksDev\Avito\Products\Entity\Images\AvitoProductImage;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\BaksDevDeliveryTransportBundle;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Product\Entity\Active\ProductActive;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Event\ProductEvent;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Offers\Image\ProductOfferImage;
use BaksDev\Products\Product\Entity\Offers\Price\ProductOfferPrice;
use BaksDev\Products\Product\Entity\Offers\ProductOffer;
use BaksDev\Products\Product\Entity\Offers\Quantity\ProductOfferQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Image\ProductVariationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Image\ProductModificationImage;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Price\ProductModificationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\ProductModification;
use BaksDev\Products\Product\Entity\Offers\Variation\Modification\Quantity\ProductModificationQuantity;
use BaksDev\Products\Product\Entity\Offers\Variation\Price\ProductVariationPrice;
use BaksDev\Products\Product\Entity\Offers\Variation\ProductVariation;
use BaksDev\Products\Product\Entity\Offers\Variation\Quantity\ProductVariationQuantity;
use BaksDev\Products\Product\Entity\Photo\ProductPhoto;
use BaksDev\Products\Product\Entity\Price\ProductPrice;
use BaksDev\Products\Product\Entity\Product;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use InvalidArgumentException;

final class AllProductsWithMapperRepository implements AllProductsWithMapperInterface
{
    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function forProfile(UserProfile|UserProfileUid $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Метод получает массив свойств продукта с маппингом и данными токена
     */
    public function findAll(): array|false
    {
        if($this->profile === false)
        {
            throw new InvalidArgumentException('Invalid Argument profile');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        /** Проверяю, есть ли соответствующий профиль */
        $dbal
            ->join(
                'product',
                AvitoToken::class,
                'avito_token',
                'avito_token.id = :profile'
            )
            ->setParameter(
                key: 'profile',
                value: $this->profile,
                type: UserProfileUid::TYPE
            );

        $dbal
            ->join(
                'avito_token',
                AvitoTokenEvent::class,
                'avito_token_event',
                '
                        avito_token_event.id = avito_token.event AND
                        avito_token_event.active = TRUE',
            );

        $dbal->join(
            'avito_token',
            UserProfileInfo::class,
            'info',
            '
                info.profile = avito_token.id AND
                info.status = :status',
        )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );


        $dbal
            ->addSelect('avito_token_percent.value AS avito_profile_percent')
            ->leftJoin(
                'avito_token',
                AvitoTokenPercent::class,
                'avito_token_percent',
                'avito_token_percent.event = avito_token.event',
            );

        $dbal
            ->addSelect('avito_token_address.value AS avito_profile_address')
            ->leftJoin(
                'avito_token',
                AvitoTokenAddress::class,
                'avito_token_address',
                'avito_token_address.event = avito_token.event',
            );


        $dbal
            ->addSelect('avito_token_manager.value AS avito_profile_manager')
            ->leftJoin(
                'avito_token',
                AvitoTokenManager::class,
                'avito_token_manager',
                'avito_token_manager.event = avito_token.event',
            );


        $dbal
            ->addSelect('avito_token_profile.value AS avito_profile_phone')
            ->leftJoin(
                'avito_token',
                AvitoTokenPhone::class,
                'avito_token_phone',
                'avito_token_phone.event = avito_token.event',
            );


        /*$dbal
            ->addSelect('avito_token_profile.address AS avito_profile_address')
            ->addSelect('avito_token_profile.percent AS avito_profile_percent')
            ->addSelect('avito_token_profile.manager AS avito_profile_manager')
            ->addSelect('avito_token_profile.phone AS avito_profile_phone')
            ->join(
                'avito_token',
                AvitoTokenProfile::class,
                'avito_token_profile',
                'avito_token_profile.event = avito_token.event'
            );*/



        $dbal->leftJoin(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );


        /** Получаем только на активные продукты */
        $dbal
            ->addSelect('product_active.active_from AS product_date_begin')
            ->addSelect('product_active.active_to AS product_date_over')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE'
            );

        $dbal
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );


        /** Получаем название с учетом настроек локализации */
        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->addSelect('product_trans.event AS product_name_event')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local'
            );

        $dbal
            ->addSelect('product_desc.preview AS product_description')
            ->leftJoin(
                'product_event',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product_event.id AND product_desc.device = :device '
            )->setParameter('device', 'pc');

        /**
         * Торговое предложение
         */
        $dbal
            ->addSelect('product_offer.id as product_offer_id')
            ->addSelect('product_offer.const as product_offer_const')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->addSelect('product_offer.category_offer as offer_section_field_uid')
            ->leftJoin(
                'product_event',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product_event.id'
            );

        /**
         * Тип торгового предложения
         */
        $dbal
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /**
         * Множественные варианты торгового предложения
         */
        $dbal
            ->addSelect('product_variation.id as product_variation_id')
            ->addSelect('product_variation.const as product_variation_const')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->addSelect('product_variation.category_variation as variation_section_field_uid')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );

        /**
         * Модификация множественного варианта
         */
        $dbal
            ->addSelect('product_modification.id as product_modification_id')
            ->addSelect('product_modification.const as product_modification_const')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->addSelect('product_modification.category_modification as modification_section_field_uid')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id '
            );

        /**
         * Артикул продукта
         */
        $dbal->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');

        /**
         * Категория
         */
        $dbal
            ->leftJoin(
                'product_event',
                ProductCategory::class,
                'product_category',
                'product_category.event = product_event.id AND product_category.root = true'
            );

        $dbal->join(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category'
        );

        /** Получаем только на активные категории */
        $dbal
            ->addSelect('category_info.active as category_active')
            ->join(
                'product_category',
                CategoryProductInfo::class,
                'category_info',
                '
                    category.event = category_info.event AND
                    category_info.active IS TRUE'
            );

        $dbal
            ->addSelect('category_trans.name AS product_category')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );

        /**
         * Базовая Цена товара
         */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        )
            ->addGroupBy('product_price.reserve');

        /**
         * Цена торгового предо жения
         */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        );

        /**
         * Цена множественного варианта
         */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );

        /**
         * Цена модификации множественного варианта
         */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );

        /**
         * Стоимость продукта
         */
        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.price
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
			   THEN product_variation_price.price
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.price
			   
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
			   THEN product_price.price
			   
			   ELSE NULL
			END AS product_price'
        );

        /* Предыдущая стоимость продукта */

        $dbal->addSelect("
			COALESCE(
                NULLIF(product_modification_price.old, 0),
                NULLIF(product_variation_price.old, 0),
                NULLIF(product_offer_price.old, 0),
                NULLIF(product_price.old, 0),
                0
            ) AS product_old_price
		");

        /**
         * Валюта продукта
         */
        $dbal->addSelect(
            '
			CASE
			
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.currency
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0 
			   THEN product_variation_price.currency
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL AND product_price.price > 0 
			   THEN product_price.currency
			   
			   ELSE NULL
			   
			END AS product_currency'
        );

        /** Наличие продукта */
        /**
         * Наличие и резерв торгового предложения
         */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        );

        /**
         * Наличие и резерв множественного варианта
         */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        );

        $dbal->leftJoin(
            'product_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        );

        $dbal->addSelect(
            '
            CASE
			   WHEN product_modification_quantity.quantity > 0 AND product_modification_quantity.quantity > product_modification_quantity.reserve 
			   THEN (product_modification_quantity.quantity - product_modification_quantity.reserve)
			
			   WHEN product_variation_quantity.quantity > 0 AND product_variation_quantity.quantity > product_variation_quantity.reserve 
			   THEN (product_variation_quantity.quantity - product_variation_quantity.reserve)
			
			   WHEN product_offer_quantity.quantity > 0 AND product_offer_quantity.quantity > product_offer_quantity.reserve 
			   THEN (product_offer_quantity.quantity - product_offer_quantity.reserve)
			  
			   WHEN product_price.quantity > 0 AND product_price.quantity > product_price.reserve 
			   THEN (product_price.quantity - product_price.reserve)
			 
			   ELSE 0
			END AS product_quantity'
        );

        /** Фото продукции*/

        /**
         * Фото модификаций
         */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            'product_modification_image.modification = product_modification.id'
        );

        /**
         * Фото вариантов
         */
        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            'product_variation_image.variation = product_variation.id'
        );

        /**
         * Фото торговых предложений
         */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            'product_offer_images.offer = product_offer.id'
        );

        /**
         * Фото продукта
         */
        $dbal->leftJoin(
            'product',
            ProductPhoto::class,
            'product_photo',
            'product_photo.event = product.event'
        );

        $dbal->addSelect(
            "JSON_AGG 
            (DISTINCT
				CASE 
                    WHEN product_offer_images.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_offer_images.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name),
                            'img_ext', product_offer_images.ext,
                            'img_cdn', product_offer_images.cdn
                        ) 
                    
                    WHEN product_variation_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_variation_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_image.name),
                            'img_ext', product_variation_image.ext,
                            'img_cdn', product_variation_image.cdn
                        )	
                    
                    WHEN product_modification_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_modification_image.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_image.name),
                            'img_ext', product_modification_image.ext,
                            'img_cdn', product_modification_image.cdn
                        )
                    
                    WHEN product_photo.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'img_root', product_photo.root,
                            'img', CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name),
                            'img_ext', product_photo.ext,
                            'img_cdn', product_photo.cdn
                        )
                    END) AS product_images"
        );

        /**  Вес продукта  */
        if(class_exists(BaksDevDeliveryTransportBundle::class))
        {
            $dbal
                ->addSelect('product_package.length AS product_length_delivery')
                ->addSelect('product_package.width AS product_width_delivery')
                ->addSelect('product_package.height AS product_height_delivery')
                ->addSelect('product_package.weight AS product_weight_delivery')
                ->leftJoin(
                    'product_modification',
                    DeliveryPackageProductParameter::class,
                    'product_package',
                    'product_package.product = product.id AND 
                    
                    (
                        (product_offer.const IS NOT NULL AND product_package.offer = product_offer.const) OR 
                        (product_offer.const IS NULL AND product_package.offer IS NULL)
                    )
                    
                    AND
                     
                    (
                        (product_variation.const IS NOT NULL AND product_package.variation = product_variation.const) OR 
                        (product_variation.const IS NULL AND product_package.variation IS NULL)
                    )
                     
                   AND
                   
                   (
                        (product_modification.const IS NOT NULL AND product_package.modification = product_modification.const) OR 
                        (product_modification.const IS NULL AND product_package.modification IS NULL)
                   )
                ');
        }

        /** Avito mapper */

        /**
         * Категория, для которой создан маппер. Для каждой карточки
         */
        $dbal
            ->addSelect('avito_board.id AS avito_board_mapper_category_id')
            ->leftJoin(
                'product_category',
                AvitoBoard::class,
                'avito_board',
                'avito_board.id = product_category.category'
            );

        /**
         * Название категории в Авито из активного события маппера. Для каждой карточки
         */
        $dbal
            ->addSelect('avito_board_event.avito AS avito_board_avito_category')
            ->leftJoin(
                'avito_board',
                AvitoBoardEvent::class,
                'avito_board_event',
                'avito_board_event.id = avito_board.event'
            );

        $dbal
            ->leftJoin(
                'avito_board',
                AvitoBoardMapperElement::class,
                'avito_mapper',
                'avito_mapper.event = avito_board.event'
            );

        /** Получаем значение из СВОЙСТВ товара */
        $dbal
            ->leftJoin(
                'avito_mapper',
                ProductProperty::class,
                'product_property',
                '
                product_property.event = product.event AND 
                product_property.field = avito_mapper.product_field'
            );

        /**
         * Получаем значение из торговых предложений
         */
        $dbal
            ->leftJoin(
                'avito_mapper',
                ProductOffer::class,
                'product_offer_params',
                '
                    product_offer_params.id = product_offer.id AND  
                    product_offer_params.category_offer = avito_mapper.product_field'
            );

        /**
         * Получаем значение из вариантов модификации множественного варианта
         */
        $dbal
            ->leftJoin(
                'avito_mapper',
                ProductVariation::class,
                'product_variation_params',
                '
                    product_variation_params.id = product_variation.id AND 
                    product_variation_params.category_variation = avito_mapper.product_field'
            );

        /**
         * Получаем значение из модификаций множественного варианта
         */
        $dbal
            ->leftJoin(
                'avito_mapper',
                ProductModification::class,
                'product_modification_params',
                '
                    product_modification_params.id = product_modification.id AND 
                    product_modification_params.category_modification = avito_mapper.product_field'
            );


        $dbal->addSelect(
            "JSON_AGG
			(
                DISTINCT
					JSONB_BUILD_OBJECT
                        (
                            'element', avito_mapper.element,
                
                            'value', 
                                (CASE
                                   WHEN product_property.value IS NOT NULL THEN product_property.value
                                   WHEN product_offer_params.value IS NOT NULL THEN product_offer_params.value
                                   WHEN product_modification_params.value IS NOT NULL THEN product_modification_params.value
                                   WHEN product_variation_params.value IS NOT NULL THEN product_variation_params.value
                                   WHEN avito_mapper.def IS NOT NULL THEN avito_mapper.def
                                   ELSE NULL
                                END)
                        )
			) 
			AS avito_board_mapper"
        );

        /** Продукт Авито */
        $dbal
            ->addSelect('avito_product.description as avito_product_description')
            ->leftJoin(
                'product_modification',
                AvitoProduct::class,
                'avito_product',
                '
                avito_product.product = product.id AND 
                (avito_product.offer IS NULL OR avito_product.offer = product_offer.const) AND 
                (avito_product.variation IS NULL OR avito_product.variation = product_variation.const) AND 
                (avito_product.modification IS NULL OR avito_product.modification = product_modification.const)
            '
            );

        /** Изображения Авито */
        $dbal->leftJoin(
            'avito_product',
            AvitoProductImage::class,
            'avito_product_images',
            '
                avito_product_images.avito = avito_product.id'
        );

        $dbal->addSelect(
            "JSON_AGG
            (DISTINCT
                CASE
                    WHEN avito_product_images.name IS NOT NULL THEN JSONB_BUILD_OBJECT
                    (
                        'img_root', avito_product_images.root,
                        'img', CONCAT ( '/upload/".$dbal->table(AvitoProductImage::class)."' , '/', avito_product_images.name),
                        'img_ext', avito_product_images.ext,
                        'img_cdn', avito_product_images.cdn
                    )
                    ELSE NULL
                END
            ) as avito_product_images
            "
        );

        $dbal->allGroupByExclude();

        $dbal->where('avito_board.id IS NOT NULL AND avito_board_event.category IS NOT NULL');

        $result = $dbal
            ->enableCache('orders-order', '1 day')
            ->fetchAllAssociative();

        if(empty($result))
        {
            return false;
        }

        return $result;
    }
}
