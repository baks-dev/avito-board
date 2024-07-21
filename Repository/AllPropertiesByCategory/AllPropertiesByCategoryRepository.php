<?php

namespace BaksDev\Avito\Board\Repository\AllPropertiesByCategory;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Device\Device;
use BaksDev\Core\Type\Device\Devices\Desktop;
use BaksDev\DeliveryTransport\BaksDevDeliveryTransportBundle;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Offers\Variation\CategoryProductVariation;
use BaksDev\Products\Category\Entity\Offers\Variation\Modification\CategoryProductModification;
use BaksDev\Products\Category\Entity\Section\CategoryProductSection;
use BaksDev\Products\Category\Entity\Section\Field\CategoryProductSectionField;
use BaksDev\Products\Category\Entity\Section\Field\Trans\CategoryProductSectionFieldTrans;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Product\Entity\Category\ProductCategory;
use BaksDev\Products\Product\Entity\Description\ProductDescription;
use BaksDev\Products\Product\Entity\Info\ProductInfo;
use BaksDev\Products\Product\Entity\Modify\ProductModify;
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

class AllPropertiesByCategoryRepository implements AllPropertiesByCategoryInterface
{
    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    /** Метод возвращает все товары в категории */
    public function fetchAllProductByCategory(CategoryProductUid $category): array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal->addSelect('product_category.category');

        $dbal
            ->from(ProductCategory::class, 'product_category')
            ->where('product_category.category = :category AND product_category.root = true')
            ->setParameter('category', $category, CategoryProductUid::TYPE);

        $dbal->join(
            'product_category',
            Product::class,
            'product',
            'product.event = product_category.event'
        );

        $dbal->addSelect('product.id');

        $dbal
            ->addSelect('product_info.url')
            ->addSelect('product_info.sort')
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id'
            );

        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product.event AND product_trans.local = :local'
            );

        $dbal
            ->addSelect('product_desc.preview')
            ->leftJoin(
                'product',
                ProductDescription::class,
                'product_desc',
                'product_desc.event = product.event AND product_desc.local = :local AND product_desc.device = :device'
            )->setParameter('device', new Device(Desktop::class), Device::TYPE);

        $dbal
            ->addSelect('product_modify.mod_date AS modify', )
            ->leftJoin(
                'product',
                ProductModify::class,
                'product_modify',
                'product_modify.event = product.event'
            );

        /** Торговое предложение */
        $dbal
            ->addSelect('product_offer.value AS offer_value', )
            ->addSelect('product_offer.postfix AS offer_postfix', )
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event'
            );

        /* Цена торгового предложения */
        $dbal->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferQuantity::class,
            'product_offer_quantity',
            'product_offer_quantity.offer = product_offer.id'
        );

        /* Получаем тип торгового предложения */
        $dbal
            ->addSelect('category_offer.reference as offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer'
            );

        /** Множественный вариант */
        $dbal
            ->addSelect('product_variation.value AS variation_value')
            ->addSelect('product_variation.postfix AS variation_postfix')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id'
            );

        $dbal->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id'
        );

        $dbal->leftJoin(
            'category_variation',
            ProductVariationQuantity::class,
            'product_variation_quantity',
            'product_variation_quantity.variation = product_variation.id'
        );

        $dbal
            ->addSelect('category_variation.reference as variation_reference')
            ->leftJoin(
                'product_variation',
                CategoryProductVariation::class,
                'category_variation',
                'category_variation.id = product_variation.category_variation'
            );

        /** Модификация множественного варианта торгового предложения */
        $dbal
            ->addSelect('product_modification.value AS modification_value')
            ->addSelect('product_modification.postfix AS modification_postfix')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id'
            );

        /** Цена множественного варианта */
        $dbal->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id'
        );

        $dbal->leftJoin(
            'category_modification',
            ProductModificationQuantity::class,
            'product_modification_quantity',
            'product_modification_quantity.modification = product_modification.id'
        );

        /** Получаем тип модификации множественного варианта */
        $dbal
            ->addSelect('category_modification.reference as modification_reference')
            ->leftJoin(
                'product_modification',
                CategoryProductModification::class,
                'category_modification',
                'category_modification.id = product_modification.category_modification'
            );

        /** Цена товара */
        $dbal->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event'
        );

        /** Идентификатор */
        $dbal->addSelect(
            "
			CASE
			   WHEN product_modification.const IS NOT NULL 
			   THEN product_modification.const
			   
			   WHEN product_variation.const IS NOT NULL 
			   THEN product_variation.const
			   
			   WHEN product_offer.const IS NOT NULL 
			   THEN product_offer.const
			   
			   ELSE product.id
			END AS product_id
		"
        );

        /** Стоимость */
        $dbal->addSelect(
            "
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.price
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0  
			   THEN product_variation_price.price
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.price
			   
			   WHEN product_price.price IS NOT NULL 
			   THEN product_price.price
			   
			   ELSE NULL
			END AS product_price
		"
        );

        /** Валюта продукта */
        $dbal->addSelect(
            "
			CASE
			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0 
			   THEN product_modification_price.currency
			   
			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0  
			   THEN product_variation_price.currency
			   
			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0 
			   THEN product_offer_price.currency
			   
			   WHEN product_price.price IS NOT NULL 
			   THEN product_price.currency
			   
			   ELSE NULL
			END AS product_currency
		"
        )
            ->addGroupBy('product_modification_price.currency')
            ->addGroupBy('product_variation_price.currency')
            ->addGroupBy('product_offer_price.currency')
            ->addGroupBy('product_price.currency');


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
			END AS product_quantity
		'
        )
            ->addGroupBy('product_modification_quantity.reserve')
            ->addGroupBy('product_variation_quantity.reserve')
            ->addGroupBy('product_offer_quantity.reserve')
            ->addGroupBy('product_price.reserve');

        /** Фото продукта */

        $dbal->leftJoin(
            'product_modification',
            ProductModificationImage::class,
            'product_modification_image',
            '
			product_modification_image.modification = product_modification.id AND
			product_modification_image.root = true
			'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductVariationImage::class,
            'product_variation_image',
            '
			product_variation_image.variation = product_variation.id AND
			product_variation_image.root = true
			'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductOfferImage::class,
            'product_offer_images',
            '
			product_variation_image.name IS NULL AND
			product_offer_images.offer = product_offer.id AND
			product_offer_images.root = true
			'
        );

        $dbal->leftJoin(
            'product_offer',
            ProductPhoto::class,
            'product_photo',
            '
			product_offer_images.name IS NULL AND
			product_photo.event = product.event AND
			product_photo.root = true
			'
        );

        $dbal->addSelect(
            "
			CASE
			 WHEN product_modification_image.name IS NOT NULL 
			 THEN CONCAT ( '/upload/" . $dbal->table(ProductModificationImage::class) . "' , '/', product_modification_image.name)
					
			   WHEN product_variation_image.name IS NOT NULL 
			   THEN CONCAT ( '/upload/" . $dbal->table(ProductVariationImage::class) . "' , '/', product_variation_image.name)
					
			   WHEN product_offer_images.name IS NOT NULL 
			   THEN CONCAT ( '/upload/" . $dbal->table(ProductOfferImage::class) . "' , '/', product_offer_images.name)
					
			   WHEN product_photo.name IS NOT NULL 
			   THEN CONCAT ( '/upload/" . $dbal->table(ProductPhoto::class) . "' , '/', product_photo.name)
					
			   ELSE NULL
			END AS product_image
		"
        );

        /** Расширение файла */
        $dbal->addSelect(
            "
			CASE
                WHEN product_modification_image.ext IS NOT NULL AND product_modification_image.name IS NOT NULL 
                THEN product_modification_image.ext
					
			   WHEN product_variation_image.ext IS NOT NULL AND product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.ext
					
			   WHEN product_offer_images.ext IS NOT NULL AND product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.ext
					
			   WHEN product_photo.ext IS NOT NULL AND product_photo.name IS NOT NULL 
			   THEN product_photo.ext
					
			   ELSE NULL
			END AS product_image_ext
		"
        );

        /** Флаг загрузки файла CDN */
        $dbal->addSelect(
            "
			CASE
			    WHEN product_modification_image.cdn IS NOT NULL AND product_modification_image.name IS NOT NULL 
			    THEN product_modification_image.cdn
					
			   WHEN product_variation_image.cdn IS NOT NULL AND product_variation_image.name IS NOT NULL 
			   THEN product_variation_image.cdn
					
			   WHEN product_offer_images.cdn IS NOT NULL AND product_offer_images.name IS NOT NULL 
			   THEN product_offer_images.cdn
					
			   WHEN product_photo.cdn IS NOT NULL AND product_photo.name IS NOT NULL 
			   THEN product_photo.cdn
					
			   ELSE NULL
			END AS product_image_cdn
		"
        );

        /** Свойства, учавствующие в карточке */

        $dbal->leftJoin(
            'product_category',
            CategoryProduct::class,
            'category',
            'category.id = product_category.category'
        );

        $dbal
            ->addSelect('category_info.url AS category_url')
            ->leftJoin(
                'category',
                CategoryProductInfo::class,
                'category_info',
                'category_info.event = category.event'
            );

        $dbal
            ->addSelect('category_trans.name AS category_name')
            ->addSelect('category_trans.description AS category_desc')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local'
            );

        $dbal->leftJoin(
            'category',
            CategoryProductSection::class,
            'category_section',
            'category_section.event = category.event'
        );


        $dbal->leftJoin(
            'category_section',
            CategoryProductSectionField::class,
            'category_section_field',
            'category_section_field.section = category_section.id 
            AND category_section_field.card = TRUE'
        );

        $dbal->leftJoin(
            'category_section_field',
            CategoryProductSectionFieldTrans::class,
            'category_section_field_trans',
            'category_section_field_trans.field = category_section_field.id 
            AND category_section_field_trans.local = :local'
        );


        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'product_property',
            'product_property.event = product.event 
            AND product_property.field = category_section_field.const'
        );


        /* Артикул продукта */

        $dbal->addSelect(
            '
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
			END AS product_article
		'
        );


        /* Артикул продукта */

        $dbal->addSelect(
            '
			CASE
			   WHEN product_modification.barcode IS NOT NULL 
			   THEN product_modification.barcode
			   
			   WHEN product_variation.barcode IS NOT NULL 
			   THEN product_variation.barcode
			   
			   WHEN product_offer.barcode IS NOT NULL 
			   THEN product_offer.barcode
			   
			   WHEN product_info.barcode IS NOT NULL 
			   THEN product_info.barcode
			   
			   ELSE NULL
			END AS product_barcode
		'
        );

        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT

				JSONB_BUILD_OBJECT
				(
					'0', category_section_field.sort,
					'field_uid', category_section_field.id,
					'field_const', category_section_field.const,
					'field_name', category_section_field.name,
					'field_card', category_section_field.card,
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,
					'field_value', product_property.value
				)

		)
			AS category_section_field"
        );

        /**  Вес товара  */

        if (class_exists(BaksDevDeliveryTransportBundle::class))
        {

            $dbal
                ->addSelect('product_parameter.length AS product_parameter_length')
                ->addSelect('product_parameter.width AS product_parameter_width')
                ->addSelect('product_parameter.height AS product_parameter_height')
                ->addSelect('product_parameter.weight AS product_parameter_weight')
                ->leftJoin(
                    'product_modification',
                    DeliveryPackageProductParameter::class,
                    'product_parameter',
                    'product_parameter.product = product.id AND
            (product_parameter.offer IS NULL OR product_parameter.offer = product_offer.const) AND
            (product_parameter.variation IS NULL OR product_parameter.variation = product_variation.const) AND
            (product_parameter.modification IS NULL OR product_parameter.modification = product_modification.const)

        '
                );
        }

        $dbal->addOrderBy('product_info.sort', 'DESC');

        $dbal->allGroupByExclude();

        return $dbal->enableCache('products-product', 86400)->fetchAllAssociative();

    }
}
