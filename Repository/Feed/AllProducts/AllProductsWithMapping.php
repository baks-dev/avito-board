<?php

declare(strict_types=1);

namespace BaksDev\Avito\Board\Repository\Feed\AllProducts;

use BaksDev\Avito\Board\Entity\AvitoBoard;
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Board\Entity\Mapper\AvitoBoardMapper;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
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

final class AllProductsWithMapping implements AllProductsWithMappingInterface
{
    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    /**
     * Метод получает массив элементов продукции с соотношением свойств
     */
    public function findAll(): array|bool
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        // @TODO если ли смысл в этом объединении, так как в корне и тк активное событие, а из события никакой полезной информации не получить
        $dbal->leftJoin(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );


        $dbal
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                'product_active.event = product.event AND product_active.active IS TRUE'
            );


        $dbal
//                    ->addSelect('product_info.url')
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        /** Получаем название с учетом настроек локализации */
        $dbal
            ->addSelect('product_trans.name AS product_name')
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
        $dbal->addSelect(
            "
					CASE
					   WHEN product_modification.article IS NOT NULL 
					   THEN product_modification.article
					   
					   WHEN product_variation.article IS NOT NULL 
					   THEN product_variation.article
					   
					   WHEN product_offer.article IS NOT NULL 
					   THEN product_offer.article
					   
					   WHEN product_info.article IS NOT NULL 
					   THEN product_info.article
					   
					   ELSE NULL
					END AS product_article"
        );

        // @TODO ProductCategory и CategoryProduct содержат одинаковые данные??
        /**
         * Категория
         */
        $dbal
            ->join(
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
        )
            ->addGroupBy('product_offer_quantity.reserve');

        /**
         * Наличие и резерв множественного варианта
         */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        )
            ->addGroupBy('product_variation_quantity.reserve');

        $dbal->leftJoin(
            'product_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        )
            ->addGroupBy('product_modification_quantity.reserve');


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
                            'product_img_root', product_offer_images.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductOfferImage::class) . "' , '/', product_offer_images.name),
                            'product_img_ext', product_offer_images.ext,
                            'product_img_cdn', product_offer_images.cdn
                        ) 
                    WHEN product_variation_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_variation_image.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductVariationImage::class) . "' , '/', product_variation_image.name),
                            'product_img_ext', product_variation_image.ext,
                            'product_img_cdn', product_variation_image.cdn
                        )	
                    WHEN product_modification_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_modification_image.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductModificationImage::class) . "' , '/', product_modification_image.name),
                            'product_img_ext', product_modification_image.ext,
                            'product_img_cdn', product_modification_image.cdn
                        )
                    WHEN product_photo.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_photo.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductPhoto::class) . "' , '/', product_photo.name),
                            'product_img_ext', product_photo.ext,
                            'product_img_cdn', product_photo.cdn
                        )
                    END) AS product_images"
        );


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
            ->addSelect('avito_event.avito AS avito_board_avito')
            ->leftJoin(
                'avito_board',
                AvitoBoardEvent::class,
                'avito_event',
                'avito_event.id = avito_board.event'
            );


        $dbal
//            ->addSelect('avito_mapper.element AS avito_mapper_element')
            //->addSelect('avito_mapper.def AS avito_mapper_default')
            ->leftJoin(
                'avito_board',
                AvitoBoardMapper::class,
                'avito_mapper',
                'avito_mapper.event = avito_board.event'
            );


        //        $dbal->addSelect(
        //            "JSON_AGG
        //            ( DISTINCT
        //                    JSONB_BUILD_OBJECT
        //                    (
        //                        'avito_mapper_element', avito_mapper.element,
        //                        'avito_mapper_field', avito_mapper.product_field,
        //                        'avito_mapper_default', avito_mapper.def
        //                    )
        //            )
        //            AS avito_mapper"
        //        );

        //        $dbal->allGroupByExclude();
        //        dd($dbal->fetchAllAssociative());

        /** Получаем значение из свойств товара */
        $dbal
//            ->addSelect('product_property.value AS product_property_value')
            ->leftJoin(
                'avito_mapper',
                ProductProperty::class,
                'product_property',
                'product_property.event = product.event AND product_property.field = avito_mapper.product_field'
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
			AS product_property"
        );


        $dbal->allGroupByExclude();

        return $dbal
            // ->enableCache('Namespace', 3600)
            ->fetchAllAssociative();
    }

    public function findAllWithMapper(): array|bool
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        // @TODO если ли смысл в этом объединении, так как в корне и тк активное событие, а из события никакой полезной информации не получить
        $dbal->leftJoin(
            'product',
            ProductEvent::class,
            'product_event',
            'product_event.id = product.event'
        );


        $dbal
            ->addSelect('product_active.active_from as product_active_from')
            ->addSelect('product_active.active_to as product_active_to')
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                'product_active.event = product.event AND product_active.active IS TRUE'
            );


        $dbal
//                    ->addSelect('product_info.url')
            ->leftJoin(
                'product_event',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        /** Получаем название с учетом настроек локализации */
        $dbal
            ->addSelect('product_trans.name AS product_name')
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
        $dbal->addSelect(
            "
					CASE
					   WHEN product_modification.article IS NOT NULL 
					   THEN product_modification.article
					   
					   WHEN product_variation.article IS NOT NULL 
					   THEN product_variation.article
					   
					   WHEN product_offer.article IS NOT NULL 
					   THEN product_offer.article
					   
					   WHEN product_info.article IS NOT NULL 
					   THEN product_info.article
					   
					   ELSE NULL
					END AS product_article"
        );

        // @TODO ProductCategory и CategoryProduct содержат одинаковые данные??
        /**
         * Категория
         */
        $dbal
            ->join(
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

        $dbal
            ->addSelect('category_trans.name AS product_category')
            ->addSelect('category.id AS product_category_id')
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
        )
            ->addGroupBy('product_offer_quantity.reserve');

        /**
         * Наличие и резерв множественного варианта
         */
        $dbal->leftJoin(
            'product_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        )
            ->addGroupBy('product_variation_quantity.reserve');

        $dbal->leftJoin(
            'product_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        )
            ->addGroupBy('product_modification_quantity.reserve');


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
                            'product_img_root', product_offer_images.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductOfferImage::class) . "' , '/', product_offer_images.name),
                            'product_img_ext', product_offer_images.ext,
                            'product_img_cdn', product_offer_images.cdn
                        ) 
                    WHEN product_variation_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_variation_image.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductVariationImage::class) . "' , '/', product_variation_image.name),
                            'product_img_ext', product_variation_image.ext,
                            'product_img_cdn', product_variation_image.cdn
                        )	
                    WHEN product_modification_image.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_modification_image.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductModificationImage::class) . "' , '/', product_modification_image.name),
                            'product_img_ext', product_modification_image.ext,
                            'product_img_cdn', product_modification_image.cdn
                        )
                    WHEN product_photo.ext IS NOT NULL 
                    THEN JSONB_BUILD_OBJECT
                        (
                            'product_img_root', product_photo.root,
                            'product_img', CONCAT ( '/upload/" . $dbal->table(ProductPhoto::class) . "' , '/', product_photo.name),
                            'product_img_ext', product_photo.ext,
                            'product_img_cdn', product_photo.cdn
                        )
                    END) AS product_images"
        );

        /** Получаем значения из свойств товара */
        $dbal
            ->leftJoin(
                'avito_board_mapper',
                ProductProperty::class,
                'product_property',
                'product_property.event = product.event
                '
            );

        $dbal->addSelect(
            "JSON_AGG
                    ( DISTINCT
                            JSONB_BUILD_OBJECT
                            (
                                'product_property', product_property.field
                            )
                    )
                    AS product_properties"
        );


        /** Avito mapper */
        /**
         * Категория, для которой создан маппер. Для каждой карточки
         */
        $dbal
            ->addSelect('avito_board.id AS mapper_category_id')
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
            ->addSelect('avito_board_event.avito AS avito_category')
            ->leftJoin(
                'avito_board',
                AvitoBoardEvent::class,
                'avito_board_event',
                'avito_board_event.id = avito_board.event'
            );


        $dbal
            ->leftJoin(
                'avito_board',
                AvitoBoardMapper::class,
                'avito_board_mapper',
                'avito_board_mapper.event = avito_board.event'
            );


        $dbal->addSelect(
            "JSON_AGG
                    ( DISTINCT
                            JSONB_BUILD_OBJECT
                            (
                                'avito_mapper_element', avito_board_mapper.element,
                                'avito_mapper_field', avito_board_mapper.product_field,
                                'avito_mapper_default', avito_board_mapper.def
                            )
                    )
                    AS avito_mapper"
        );


        $dbal->allGroupByExclude();

        return $dbal
            // ->enableCache('Namespace', 3600)
            ->fetchAllAssociative();
    }
}
