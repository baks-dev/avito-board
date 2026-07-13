<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
use BaksDev\Avito\Board\Entity\Event\AvitoBoardEvent;
use BaksDev\Avito\Entity\AvitoToken;
use BaksDev\Avito\Entity\Event\Address\AvitoTokenAddress;
use BaksDev\Avito\Entity\Event\Kit\AvitoTokenKit;
use BaksDev\Avito\Entity\Event\Manager\AvitoTokenManager;
use BaksDev\Avito\Entity\Event\Percent\AvitoTokenPercent;
use BaksDev\Avito\Entity\Event\Phone\AvitoTokenPhone;
use BaksDev\Avito\Entity\Event\Profile\AvitoTokenProfile;
use BaksDev\Avito\Products\Entity\AvitoProduct;
use BaksDev\Avito\Products\Entity\Images\AvitoProductImage;
use BaksDev\Avito\Products\Entity\Kit\AvitoProductKit;
use BaksDev\Avito\Products\Entity\Token\AvitoProductToken;
use BaksDev\Avito\Type\Id\AvitoTokenUid;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\DeliveryTransport\BaksDevDeliveryTransportBundle;
use BaksDev\DeliveryTransport\Entity\ProductParameter\DeliveryPackageProductParameter;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Products\Category\Entity\Info\CategoryProductInfo;
use BaksDev\Products\Category\Entity\Offers\CategoryProductOffers;
use BaksDev\Products\Category\Entity\Trans\CategoryProductTrans;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
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
use BaksDev\Products\Product\Entity\ProductInvariable;
use BaksDev\Products\Product\Entity\Project\Description\ProductProjectDescription;
use BaksDev\Products\Product\Entity\Project\ProductProject;
use BaksDev\Products\Product\Entity\Project\Season\ProductProjectSeason;
use BaksDev\Products\Product\Entity\Property\ProductProperty;
use BaksDev\Products\Product\Entity\Trans\ProductTrans;
use BaksDev\Products\Promotion\BaksDevProductsPromotionBundle;
use BaksDev\Products\Promotion\Entity\Event\Invariable\ProductPromotionInvariable;
use BaksDev\Products\Promotion\Entity\Event\Period\ProductPromotionPeriod;
use BaksDev\Products\Promotion\Entity\Event\Price\ProductPromotionPrice;
use BaksDev\Products\Promotion\Entity\ProductPromotion;
use BaksDev\Products\Stocks\BaksDevProductsStocksBundle;
use BaksDev\Products\Stocks\Entity\Total\ProductStockTotal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Doctrine\DBAL\ParameterType;
use Generator;
use InvalidArgumentException;

final class AllProductsWithMapperRepository implements AllProductsWithMapperInterface
{
    private AvitoTokenUid|false $token = false;

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    /** @depricated */
    public function forProfile(UserProfile|UserProfileUid $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    public function forAvitoToken(AvitoTokenUid $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Метод получает массив элементов продукции с соотношением свойств
     *
     * @return Generator<int, AllProductsWithMapperResult>|false
     * */
    public function findAll(): Generator|false
    {
        if(false === ($this->token instanceof AvitoTokenUid))
        {
            throw new InvalidArgumentException('Invalid Argument profile');
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();


        $cteSelect = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $cteSelect
            ->select('product.id')
            ->addSelect('product.event')
            ->from(Product::class, 'product');

        /** Получаем только на активные продукты */
        $cteSelect
            ->join(
                'product',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = product.event AND 
                    product_active.active IS TRUE',
            );


        $cteSelect
            ->leftJoin(
                'product',
                ProductInfo::class,
                'product_info',
                'product_info.product = product.id',
            );


        /** --------------------------------- */

        $cteSelect
            ->addSelect('product_offer.id as product_offer_id')
            ->addSelect('product_offer.const as product_offer_const')
            ->addSelect('product_offer.value as product_offer_value')
            ->addSelect('product_offer.postfix as product_offer_postfix')
            ->addSelect('product_offer.category_offer as offer_section_field_uid')
            ->leftJoin(
                'product',
                ProductOffer::class,
                'product_offer',
                'product_offer.event = product.event',
            );


        $cteSelect
            ->addSelect('product_variation.id as product_variation_id')
            ->addSelect('product_variation.const as product_variation_const')
            ->addSelect('product_variation.value as product_variation_value')
            ->addSelect('product_variation.postfix as product_variation_postfix')
            ->addSelect('product_variation.category_variation as variation_section_field_uid')
            ->leftJoin(
                'product_offer',
                ProductVariation::class,
                'product_variation',
                'product_variation.offer = product_offer.id',
            );

        $cteSelect
            ->addSelect('product_modification.id as product_modification_id')
            ->addSelect('product_modification.const as product_modification_const')
            ->addSelect('product_modification.value as product_modification_value')
            ->addSelect('product_modification.postfix as product_modification_postfix')
            ->addSelect('product_modification.category_modification as modification_section_field_uid')
            ->leftJoin(
                'product_variation',
                ProductModification::class,
                'product_modification',
                'product_modification.variation = product_variation.id ',
            );

        /** --------------------------------- */


        $cteSelect->leftJoin(
            'product',
            ProductPrice::class,
            'product_price',
            'product_price.event = product.event',
        );

        $cteSelect->leftJoin(
            'product_offer',
            ProductOfferPrice::class,
            'product_offer_price',
            'product_offer_price.offer = product_offer.id',
        );

        $cteSelect->leftJoin(
            'product_variation',
            ProductVariationPrice::class,
            'product_variation_price',
            'product_variation_price.variation = product_variation.id',
        );

        $cteSelect->leftJoin(
            'product_modification',
            ProductModificationPrice::class,
            'product_modification_price',
            'product_modification_price.modification = product_modification.id',
        );

        /** --------------------------------- */

        $cteSelect->addSelect(
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
			END AS product_price',
        );

        $cteSelect->addSelect(
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
			   
			END AS product_currency',
        );


        /**
         * Артикул продукта
         */
        $cteSelect->addSelect('
            COALESCE(
                product_modification.article, 
                product_variation.article, 
                product_offer.article, 
                product_info.article
            ) AS product_article
		');


        /** ProductInvariable */
        $cteSelect
            ->addSelect('product_invariable.id AS product_invariable_id')
            ->leftJoin(
                'product_modification',
                ProductInvariable::class,
                'product_invariable',
                '
                    product_invariable.product = product.id 
                    AND product_invariable.offer = product_offer.const
                    AND product_invariable.variation = product_variation.const
                    AND product_invariable.modification = product_modification.const
                    
            ');


        /**
         * Тип торгового предложения
         */
        $cteSelect
            ->addSelect('category_offer.reference as product_offer_reference')
            ->leftJoin(
                'product_offer',
                CategoryProductOffers::class,
                'category_offer',
                'category_offer.id = product_offer.category_offer',
            );


        /**
         * Категория
         */
        $cteSelect
            ->leftJoin(
                'product',
                ProductCategory::class,
                'product_category',
                'product_category.event = product.event AND product_category.root = true',
            );

        $cteSelect
            ->addSelect('category.id AS category_product_id')
            ->addSelect('category.event AS category_product_event')
            ->join(
                'product_category',
                CategoryProduct::class,
                'category',
                'category.id = product_category.category',
            );

        /** Получаем только на активные категории */
        $cteSelect
            ->addSelect('category_info.active as category_active')
            ->join(
                'product_category',
                CategoryProductInfo::class,
                'category_info',
                'category.event = category_info.event',
            );


        $cteSelect
            ->addSelect('category_trans.name AS product_category')
            ->leftJoin(
                'category',
                CategoryProductTrans::class,
                'category_trans',
                'category_trans.event = category.event AND category_trans.local = :local',
            );


        /** Только продукция, у которых указана стоимость */
        $cteSelect->andWhere('COALESCE(product_modification_price.price, product_variation_price.price, product_offer_price.price, product_price.price, 0) > 0 ');


        $cteSelect->andWhere('
        
       EXISTS (
              SELECT 1 FROM avito_board b
              JOIN avito_board_event be ON be.id = b.event
              WHERE b.id = category.id AND be.category IS NOT NULL
          )');


        /**
         * END cteSelect ===============================
         */


        $dbal
            ->with('cte_products', $cteSelect)
            ->from('cte_products', 'cteSelect');

        $dbal
            ->select('product.id')
            ->addSelect('product.event')
            ->join(
                'cteSelect',
                Product::class,
                'product',
                'product.id = cteSelect.id',
            );


        //        $dbal
        //            ->select('product.id')
        //            ->addSelect('product.event')
        //            ->from(Product::class, 'product')
        //            ->where('product.id = cteSelect.id');
        //        ;

        /** Проверяю, есть ли соответствующий профиль */
        $dbal
            ->join(
                'cteSelect',
                AvitoToken::class,
                'avito_token',
                'avito_token.id = :token',
            )
            ->setParameter(
                key: 'token',
                value: $this->token,
                type: AvitoTokenUid::TYPE,
            );

        $dbal->join(
            'cteSelect',
            AvitoTokenProfile::class,
            'avito_token_profile',
            'avito_token_profile.event = avito_token.event',
        );


        $dbal
            ->addSelect('avito_kit.value AS avito_kit_value')
            ->leftJoin(
                'avito_token',
                AvitoTokenKit::class,
                'avito_kit',
                'avito_kit.event = avito_token.event',
            );


        $dbal->join(
            'avito_token_profile',
            UserProfileInfo::class,
            'info',
            '
                info.profile = avito_token_profile.value AND
                info.status = :status',
        )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
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
            ->addSelect('avito_token_address.id AS avito_profile_address_id')
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
            ->addSelect('avito_token_phone.value AS avito_profile_phone')
            ->leftJoin(
                'avito_token',
                AvitoTokenPhone::class,
                'avito_token_phone',
                'avito_token_phone.event = avito_token.event',
            );


        $dbal->leftJoin(
            'cteSelect',
            ProductEvent::class,
            'product_event',
            'product_event.id = cteSelect.event',
        );


        /** Получаем только на активные продукты */
        $dbal
            ->addSelect('product_active.active_from AS product_date_begin')
            ->addSelect('product_active.active_to AS product_date_over')
            ->join(
                'cteSelect',
                ProductActive::class,
                'product_active',
                '
                    product_active.event = cteSelect.event AND 
                    product_active.active IS TRUE',
            );


        /** Получаем название с учетом настроек локализации */
        $dbal
            ->addSelect('product_trans.name AS product_name')
            ->leftJoin(
                'product_event',
                ProductTrans::class,
                'product_trans',
                'product_trans.event = product_event.id AND product_trans.local = :local',
            );

        //        $dbal
        //            ->addSelect('product_desc.preview AS product_description')
        //            ->leftJoin(
        //                'product_event',
        //                ProductDescription::class,
        //                'product_desc',
        //                'product_desc.event = product_event.id AND product_desc.device = :device ',
        //            )->setParameter('device', 'pc');


        //        /* Задать профиль - PROJECT_PROFILE */
        //        if(true === $dbal->isProjectProfile())
        //        {
        //            $dbal->andWhere('product_project.profile = :'.$dbal::PROJECT_PROFILE_KEY.' OR product_project.profile IS NULL');
        //        }


        /**
         * Торговое предложение
         */
        $dbal
            ->addSelect('cteSelect.product_offer_id')
            ->addSelect('cteSelect.product_offer_const')
            ->addSelect('cteSelect.product_offer_value')
            ->addSelect('cteSelect.product_offer_postfix')
            ->addSelect('cteSelect.offer_section_field_uid')


            //            ->leftJoin(
            //                'cteSelect',
            //                ProductOffer::class,
            //                'product_offer',
            //                'product_offer.id = cteSelect.product_offer_id',
            //            )
        ;


        //        /**
        //         * Тип торгового предложения
        //         */
        //        $dbal
        //            ->addSelect('category_offer.reference as product_offer_reference')
        //            ->leftJoin(
        //                'product_offer',
        //                CategoryProductOffers::class,
        //                'category_offer',
        //                'category_offer.id = product_offer.category_offer',
        //            );
        //
        //

        $dbal->addSelect('cteSelect.product_offer_reference');


        /**
         * Множественные варианты торгового предложения
         */
        $dbal
            ->addSelect('cteSelect.product_variation_id')
            ->addSelect('cteSelect.product_variation_const')
            ->addSelect('cteSelect.product_variation_value')
            ->addSelect('cteSelect.product_variation_postfix')
            ->addSelect('cteSelect.variation_section_field_uid')


            //            ->leftJoin(
            //                'cteSelect',
            //                ProductVariation::class,
            //                'product_variation',
            //                'product_variation.id = cteSelect.product_variation',
            //            )
        ;


        /**
         * Модификация множественного варианта
         */
        $dbal
            ->addSelect('cteSelect.product_modification_id')
            ->addSelect('cteSelect.product_modification_const')
            ->addSelect('cteSelect.product_modification_value')
            ->addSelect('cteSelect.product_modification_postfix')
            ->addSelect('cteSelect.modification_section_field_uid')


            //            ->leftJoin(
            //                'cteSelect',
            //                ProductModification::class,
            //                'product_modification',
            //                'product_modification.id = cteSelect.product_modification ',
            //            )
        ;

        /**
         * Артикул продукта
         */

        $dbal->addSelect('cteSelect.product_article');

        //        $dbal->addSelect('
        //            COALESCE(
        //                product_modification.article,
        //                product_variation.article,
        //                product_offer.article,
        //                product_info.article
        //            ) AS product_article
        //		');


        /**
         * Категория
         */
        //        $dbal
        //            ->leftJoin(
        //                'product_event',
        //                ProductCategory::class,
        //                'product_category',
        //                'product_category.event = product_event.id AND product_category.root = true',
        //            );

        //        $dbal->join(
        //            'product_category',
        //            CategoryProduct::class,
        //            'category',
        //            'category.id = product_category.category',
        //        );

        /** Получаем только на активные категории */
        $dbal
            ->addSelect('cteSelect.category_active')
            //            ->join(
            //                'product_category',
            //                CategoryProductInfo::class,
            //                'category_info',
            //                'category.event = category_info.event',
            //            )
        ;

        $dbal
            ->addSelect('cteSelect.product_category')
            //            ->leftJoin(
            //                'cteSelect',
            //                CategoryProductTrans::class,
            //                'category_trans',
            //                'category_trans.event = category.event AND category_trans.local = :local',
            //            )
        ;

        //        /**
        //         * Базовая Цена товара
        //         */
        //        $dbal->leftJoin(
        //            'cteSelect',
        //            ProductPrice::class,
        //            'product_price',
        //            'product_price.event = cteSelect.event',
        //        )
        //            ->addGroupBy('product_price.reserve');
        //
        //        /**
        //         * Цена торгового предо жения
        //         */
        //        $dbal->leftJoin(
        //            'product_offer',
        //            ProductOfferPrice::class,
        //            'product_offer_price',
        //            'product_offer_price.offer = product_offer.id',
        //        );
        //
        //        /**
        //         * Цена множественного варианта
        //         */
        //        $dbal->leftJoin(
        //            'product_variation',
        //            ProductVariationPrice::class,
        //            'product_variation_price',
        //            'product_variation_price.variation = product_variation.id',
        //        );
        //
        //        /**
        //         * Цена модификации множественного варианта
        //         */
        //        $dbal->leftJoin(
        //            'product_modification',
        //            ProductModificationPrice::class,
        //            'product_modification_price',
        //            'product_modification_price.modification = product_modification.id',
        //        );

        $dbal
            ->addSelect('cteSelect.product_price')
            ->addSelect('cteSelect.product_currency');


        //        /**
        //         * Стоимость продукта
        //         */
        //        $dbal->addSelect(
        //            '
        //			CASE
        //			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0
        //			   THEN product_modification_price.price
        //
        //			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0
        //			   THEN product_variation_price.price
        //
        //			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0
        //			   THEN product_offer_price.price
        //
        //			   WHEN product_price.price IS NOT NULL AND product_price.price > 0
        //			   THEN product_price.price
        //
        //			   ELSE NULL
        //			END AS product_price',
        //        );

        /* Предыдущая стоимость продукта */

        //        $dbal->addSelect("
        //			COALESCE(
        //                NULLIF(product_modification_price.old, 0),
        //                NULLIF(product_variation_price.old, 0),
        //                NULLIF(product_offer_price.old, 0),
        //                NULLIF(product_price.old, 0),
        //                0
        //            ) AS product_old_price
        //		");


        //        /**
        //         * Валюта продукта
        //         */
        //        $dbal->addSelect(
        //            '
        //			CASE
        //
        //			   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0
        //			   THEN product_modification_price.currency
        //
        //			   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0
        //			   THEN product_variation_price.currency
        //
        //			   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0
        //			   THEN product_offer_price.currency
        //
        //			   WHEN product_price.price IS NOT NULL AND product_price.price > 0
        //			   THEN product_price.currency
        //
        //			   ELSE NULL
        //
        //			END AS product_currency',
        //        );


        /**
         * Наличие продукции на складе
         * Если подключен модуль складского учета и передан идентификатор профиля
         */

        if(class_exists(BaksDevProductsStocksBundle::class))
        {


            $dbal
                ->addSelect('stock_agg.product_quantity')
                ->leftJoin(
                    'cteSelect',
                    "LATERAL (
                SELECT JSON_AGG(JSONB_BUILD_OBJECT('total', stock_sub.total, 'reserve', stock_sub.reserve)) AS product_quantity
                FROM (
                    SELECT DISTINCT s.total, s.reserve
                    FROM product_stock_total s
                    WHERE s.profile = avito_token_profile.value
                      AND s.product = product.id
                      AND s.offer = cteSelect.product_offer_const
                      AND s.variation = cteSelect.product_variation_const
                      AND s.modification = cteSelect.product_modification_const
                      AND s.total > s.reserve
                ) stock_sub
            )",
                    'stock_agg',
                    'true');


            //            $dbal
            //                ->addSelect("JSON_AGG (
            //                        DISTINCT JSONB_BUILD_OBJECT (
            //                            'total', stock.total,
            //                            'reserve', stock.reserve
            //                        )) FILTER (WHERE stock.total > stock.reserve)
            //
            //                        AS product_quantity",
            //                )
            //                ->leftJoin(
            //                    'cteSelect',
            //                    ProductStockTotal::class,
            //                    'stock',
            //                    '
            //                    stock.profile = avito_token_profile.value AND
            //                    stock.product = product.id
            //
            //                    AND stock.offer = cteSelect.product_offer_const
            //                    AND stock.variation = cteSelect.product_variation_const
            //                    AND stock.modification = cteSelect.product_modification_const
            //
            //
            //                ');

        }
        else
        {
            /* Наличие и резерв торгового предложения */
            $dbal->leftJoin(
                'cteSelect',
                ProductOfferQuantity::class,
                'product_offer_quantity',
                'product_offer_quantity.offer = cteSelect.product_offer_id',
            );

            /* Наличие и резерв множественного варианта */
            $dbal->leftJoin(
                'cteSelect',
                ProductVariationQuantity::class,
                'product_variation_quantity',
                'product_variation_quantity.variation = cteSelect.product_variation_id',
            );

            /* Наличие и резерв модификации множественного варианта */
            $dbal->leftJoin(
                'cteSelect',
                ProductModificationQuantity::class,
                'product_modification_quantity',
                'product_modification_quantity.modification = cteSelect.product_modification_id',
            );

            $dbal
                ->addSelect("JSON_AGG (
                        DISTINCT JSONB_BUILD_OBJECT (
                            
                            
                            'total', COALESCE(
                                            product_modification_quantity.quantity, 
                                            product_variation_quantity.quantity, 
                                            product_offer_quantity.quantity, 
                                            product_price.quantity,
                                            0
                                        ), 
                            
                            
                            'reserve', COALESCE(
                                            product_modification_quantity.reserve, 
                                            product_variation_quantity.reserve, 
                                            product_offer_quantity.reserve, 
                                            product_price.reserve,
                                            0
                                        )
                        ) )
            
                        AS product_quantity",
                );
        }


        //$dbal->addSelect('NULL AS product_images');


        $dbal
            ->addSelect('product_images.product_images')
            ->join(
                'avito_board',
                "LATERAL (
                
                SELECT

                 JSON_AGG
                (
                    JSONB_BUILD_OBJECT
                    (
                        'img_root', product_images.root,
                        'img', product_images.name,
                        'img_ext', product_images.ext,
                        'img_cdn', product_images.cdn
                    )
                    
                ) FILTER (WHERE product_images.ext IS NOT NULL) AS product_images


               
                FROM (
                
                
                SELECT DISTINCT 
                    product_photo.root,
                    product_photo.ext,
                    CONCAT ( '/upload/".$dbal->table(ProductPhoto::class)."' , '/', product_photo.name) AS name,
                    product_photo.cdn 
                FROM product_photo product_photo
                WHERE product_photo.event = product.event
                
                
               
                UNION
                
                
                SELECT DISTINCT 
                    product_offer_images.root,
                    product_offer_images.ext,
                    CONCAT ( '/upload/".$dbal->table(ProductOfferImage::class)."' , '/', product_offer_images.name) AS name,
                    product_offer_images.cdn 
                FROM product_offer_images product_offer_images
                WHERE product_offer_images.offer = cteSelect.product_offer_id
                
                
                UNION
                
                
                SELECT DISTINCT 
                    product_variation_images.root,
                    product_variation_images.ext,
                    CONCAT ( '/upload/".$dbal->table(ProductVariationImage::class)."' , '/', product_variation_images.name) AS name,
                    product_variation_images.cdn 
                FROM product_variation_images product_variation_images
                WHERE product_variation_images.variation = cteSelect.product_variation_id



                UNION
                
                
                SELECT DISTINCT 
                    product_modification_images.root,
                    product_modification_images.ext,
                    CONCAT ( '/upload/".$dbal->table(ProductModificationImage::class)."' , '/', product_modification_images.name) AS name,
                    product_modification_images.cdn 
                FROM product_modification_images product_modification_images
                WHERE product_modification_images.modification = cteSelect.product_modification_id


                      
                ) product_images
            )",
                'product_images',
                'true');


        /**  Вес продукта  */
        if(class_exists(BaksDevDeliveryTransportBundle::class))
        {
            $dbal
                ->addSelect('product_package.length AS product_length_delivery')
                ->addSelect('product_package.width AS product_width_delivery')
                ->addSelect('product_package.height AS product_height_delivery')
                ->addSelect('product_package.weight AS product_weight_delivery')
                ->leftJoin(
                    'cteSelect',
                    DeliveryPackageProductParameter::class,
                    'product_package',
                    'product_package.product = product.id 
                    
                    AND product_package.offer = cteSelect.product_offer_const
                    AND product_package.variation = cteSelect.product_variation_const
                    AND product_package.modification = cteSelect.product_modification_const 
                ');
        }


        /** Общая скидка (наценка) из профиля магазина */
        if(true === $dbal->bindProjectProfile())
        {

            $dbal
                ->join(
                    'cteSelect',
                    UserProfile::class,
                    'project_profile',
                    '
                        project_profile.id = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->addSelect('project_profile_discount.value AS project_discount')
                ->leftJoin(
                    'project_profile',
                    UserProfileDiscount::class,
                    'project_profile_discount',
                    '
                        project_profile_discount.event = project_profile.event',
                );
        }

        /* Получить товарную наценку (скидку) по сезонности с учетом текущего месяца */
        $dbal
            ->leftJoin(
                'cteSelect',
                ProductProject::class,
                'product_project',
                '
                    product_project.product = cteSelect.id
                    '.(true === $dbal->isProjectProfile()
                    ? 'AND product_project.profile = :'.$dbal::PROJECT_PROFILE_KEY
                    : 'AND product_project.profile IS NULL'),
            );

        $dbal
            ->addSelect('product_project_season.percent as season_percent')
            ->leftJoin(
                'product_project',
                ProductProjectSeason::class,
                'product_project_season',
                'product_project_season.project = product_project.id
                     AND product_project_season.month = :month',
            )
            ->setParameter(
                key: 'month',
                value: (int) date('n'),
                type: ParameterType::INTEGER,
            );


        $dbal
            ->addSelect('product_project_description.preview AS product_description')
            ->leftJoin(
                'product_project',
                ProductProjectDescription::class,
                'product_project_description',
                "
                    product_project_description.project = product_project.id
                    AND product_project_description.device = 'pc'
                ",
            );


        /**
         * ProductsPromotion
         */
        if(true === class_exists(BaksDevProductsPromotionBundle::class) && true === $dbal->isProjectProfile())
        {
            $dbal
                ->leftJoin(
                    'cteSelect',
                    ProductPromotionInvariable::class,
                    'product_promotion_invariable',
                    '
                        product_promotion_invariable.product = cteSelect.product_invariable_id
                        AND product_promotion_invariable.profile = :'.$dbal::PROJECT_PROFILE_KEY,
                );

            $dbal
                ->leftJoin(
                    'product_promotion_invariable',
                    ProductPromotion::class,
                    'product_promotion',
                    'product_promotion.id = product_promotion_invariable.main',
                );

            $dbal
                ->addSelect('product_promotion_price.value AS promotion_price')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPrice::class,
                    'product_promotion_price',
                    'product_promotion_price.event = product_promotion.event',
                );

            $dbal
                ->addSelect('
                CASE
                    WHEN 
                        CURRENT_DATE >= product_promotion_period.date_start
                        AND
                         (
                            product_promotion_period.date_end IS NULL OR CURRENT_DATE <= product_promotion_period.date_end
                         )
                    THEN true
                    ELSE false
                END AS promotion_active
            ')
                ->leftJoin(
                    'product_promotion',
                    ProductPromotionPeriod::class,
                    'product_promotion_period',
                    '
                        product_promotion_period.event = product_promotion.event',
                );
        }


        /**
         * Avito mapper
         */


        /** Категория, для которой создан маппер. Для каждой карточки */
        $dbal
            ->addSelect('avito_board.id AS avito_board_mapper_category_id')
            ->leftJoin(
                'cteSelect',
                AvitoBoard::class,
                'avito_board',
                'avito_board.id = cteSelect.category_product_id',
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
                'avito_board_event.id = avito_board.event',
            );


        //        $dbal
        //            ->leftJoin(
        //                'avito_board',
        //                AvitoBoardMapperElement::class,
        //                'avito_mapper',
        //                'avito_mapper.event = avito_board.event',
        //            );
        //
        //        $dbal->addSelect(
        //            "JSON_AGG
        //			(
        //                DISTINCT
        //					JSONB_BUILD_OBJECT
        //                        (
        //                            'element', avito_mapper.element,
        //                            'field', avito_mapper.product_field,
        //                            'default', avito_mapper.def
        //                        )
        //			)
        //			AS avito_board_mapper",
        //        );


        //        /** Получаем значение из СВОЙСТВ товара */
        //        $dbal
        //            ->leftJoin(
        //                'avito_mapper',
        //                ProductProperty::class,
        //                'product_property_mapper',
        //                '
        //                product_property_mapper.event = product.event AND
        //                product_property_mapper.field = avito_mapper.product_field',
        //            );

        //        /**
        //         * Получаем значение из торговых предложений
        //         */
        //        $dbal
        //            ->leftJoin(
        //                'avito_mapper',
        //                ProductOffer::class,
        //                'product_offer_params',
        //                '
        //                    product_offer_params.id = product_offer.id AND
        //                    product_offer_params.category_offer = avito_mapper.product_field',
        //            );

        //        /**
        //         * Получаем значение из вариантов модификации множественного варианта
        //         */
        //        $dbal
        //            ->leftJoin(
        //                'avito_mapper',
        //                ProductVariation::class,
        //                'product_variation_params',
        //                '
        //                    product_variation_params.id = product_variation.id AND
        //                    product_variation_params.category_variation = avito_mapper.product_field',
        //            );

        //        /**
        //         * Получаем значение из модификаций множественного варианта
        //         */
        //        $dbal
        //            ->leftJoin(
        //                'avito_mapper',
        //                ProductModification::class,
        //                'product_modification_params',
        //                '
        //                    product_modification_params.id = product_modification.id AND
        //                    product_modification_params.category_modification = avito_mapper.product_field',
        //            );


        //        $dbal->addSelect(
        //            "JSON_AGG
        //			(
        //                DISTINCT
        //					JSONB_BUILD_OBJECT
        //                        (
        //                            'element', avito_mapper.element,
        //
        //                            'value',
        //                                (CASE
        //                                   WHEN product_property_mapper.value IS NOT NULL THEN product_property_mapper.value
        //                                   WHEN product_offer_params.value IS NOT NULL THEN product_offer_params.value
        //                                   WHEN product_modification_params.value IS NOT NULL THEN product_modification_params.value
        //                                   WHEN product_variation_params.value IS NOT NULL THEN product_variation_params.value
        //                                   WHEN avito_mapper.def IS NOT NULL THEN avito_mapper.def
        //                                   ELSE NULL
        //                                END)
        //                        )
        //			)
        //			AS avito_board_mapper",
        //        );


        //        /** Продукт Авито */
        //        $dbal
        //            ->addSelect('avito_product.id as avito_product_id')
        //            ->addSelect('avito_product.description as avito_product_description')
        //            ->leftJoin(
        //                'cteSelect',
        //                AvitoProduct::class,
        //                'avito_product',
        //                '
        //                    avito_product.product = product.id
        //                    AND avito_product.offer = cteSelect.product_offer_const
        //                    AND avito_product.variation = cteSelect.product_variation_const
        //                    AND avito_product.modification = cteSelect.product_modification_const
        //            ');


        /** Продукт Авито по токену бизнес-пользователя */

        //        $dbal
        //            ->join(
        //                'avito_product',
        //                AvitoProductToken::class,
        //                'avito_product_token',
        //                '
        //                avito_product_token.avito = avito_product.id
        //                AND avito_product_token.value = avito_token.id',
        //            );


        //        /** Изображения Авито */
        //        $dbal->leftJoin(
        //            'avito_product_token',
        //            AvitoProductKit::class,
        //            'avito_product_kit',
        //            '
        //                avito_product_kit.avito = avito_product_token.avito
        //                AND avito_product_kit.value = avito_kit.value
        //            ');


        //        /** Изображения Авито */
        //        $dbal->join(
        //            'avito_product_kit',
        //            AvitoProductImage::class,
        //            'avito_product_images',
        //            'avito_product_images.avito = avito_product_kit.avito',
        //        );

        //        $dbal->addSelect(
        //            "JSON_AGG
        //            (DISTINCT
        //                CASE
        //                    WHEN avito_product_images.name IS NOT NULL THEN JSONB_BUILD_OBJECT
        //                    (
        //                        'img_root', avito_product_images.root,
        //                        'img', CONCAT ( '/upload/".$dbal->table(AvitoProductImage::class)."' , '/', avito_product_images.name),
        //                        'img_ext', avito_product_images.ext,
        //                        'img_cdn', avito_product_images.cdn
        //                    )
        //                    ELSE NULL
        //                END
        //            ) as avito_product_images
        //            ",
        //        );


        $dbal
            ->addSelect('avito_product.avito_product')
            ->join(
                'avito_kit',
                "LATERAL (
                
                SELECT
                 
                 JSON_AGG
                (
                    JSONB_BUILD_OBJECT
                        (
                            'avito_product_id', avito_product.id,
                            'avito_product_sale', avito_product.sale,
                            'avito_product_description', avito_product.description
                        )
                ) AS avito_product

               
                FROM (
                    SELECT DISTINCT 
                    
                        avito_product.id,
                        avito_product.description,
                        avito_product_sale.value AS sale

                    
                   FROM avito_product
                   
                   
                   JOIN avito_product_token avito_product_token
                   ON avito_product_token.avito = avito_product.id AND avito_product_token.value = avito_token.id
                   
                   JOIN avito_product_kit avito_product_kit
                   ON avito_product_kit.avito = avito_product_token.avito AND avito_product_kit.value = avito_kit.value

                   LEFT JOIN avito_product_sale avito_product_sale
                   ON avito_product_sale.avito = avito_product_token.avito
                   
                    
                    WHERE avito_product.product = product.id 
                    AND avito_product.offer = cteSelect.product_offer_const
                    AND avito_product.variation = cteSelect.product_variation_const
                    AND avito_product.modification = cteSelect.product_modification_const  
                    
                    
                      
                ) avito_product
            )",
                'avito_product',
                'true');


        $dbal
            ->addSelect('avito_product_images.avito_product_images')
            ->join(
                'avito_kit',
                "LATERAL (
                
                SELECT
                 
                 JSON_AGG
                (
                    JSONB_BUILD_OBJECT
                        (
                            'img_root', avito_product_images.root,
                            'img', CONCAT ( '/upload/".$dbal->table(AvitoProductImage::class)."' , '/', avito_product_images.name),
                            'img_ext', avito_product_images.ext,
                            'img_cdn', avito_product_images.cdn
                        )
                ) FILTER (WHERE avito_product_images.ext IS NOT NULL) AS avito_product_images

               
                FROM (
                    SELECT DISTINCT 
                    
                    avito_product_images.root,
                    avito_product_images.name,
                    avito_product_images.ext,
                    avito_product_images.cdn

                    
                   FROM avito_product
                    
                   JOIN avito_product_token avito_product_token
                   ON avito_product_token.avito = avito_product.id AND avito_product_token.value = avito_token.id
                    
                   JOIN avito_product_kit avito_product_kit
                   ON avito_product_kit.avito = avito_product_token.avito AND avito_product_kit.value = avito_kit.value
                  
                   LEFT JOIN avito_product_images avito_product_images ON avito_product_images.avito = avito_product_kit.avito

                    
                    WHERE avito_product.product = product.id 
                    AND avito_product.offer = cteSelect.product_offer_const
                    AND avito_product.variation = cteSelect.product_variation_const
                    AND avito_product.modification = cteSelect.product_modification_const  
                    
                    
                      
                ) avito_product_images
            )",
                'avito_product_images',
                'true');


        /**
         * СВОЙСТВА ПРОДУКТА
         */

        //        $dbal->leftJoin(
        //            'cteSelect',
        //            CategoryProductSection::class,
        //            'category_section',
        //            'category_section.event = cteSelect.category_product_event',
        //        );


        //        $dbal->leftJoin(
        //            'category_section',
        //            CategoryProductSectionField::class,
        //            'category_section_field',
        //            'category_section_field.section = category_section.id',
        //        );


        //        $dbal->leftJoin(
        //            'category_section_field',
        //            CategoryProductSectionFieldTrans::class,
        //            'category_section_field_trans',
        //            'category_section_field_trans.field = category_section_field.id AND category_section_field_trans.local = :local',
        //        );


        /*

        $dbal->leftJoin(
            'category_section_field',
            ProductProperty::class,
            'category_product_property',
            'category_product_property.event = product.event AND category_product_property.field = category_section_field.const',
        );



        $dbal->addSelect(
            "JSON_AGG
		( DISTINCT

				JSONB_BUILD_OBJECT
				(
					'field_type', category_section_field.type,
					'field_trans', category_section_field_trans.name,

					'field_id', category_product_property.field,
					'field_value', category_product_property.value
				)

		) FILTER (WHERE category_product_property.value IS NOT NULL)
			AS product_property",
        );


        */


        /**
         * product_field => CategoryProductSectionFieldUid
         */
        $dbal
            ->addSelect('avito_board_mapper.avito_board_mapper')
            ->join(
                'avito_board',
                "LATERAL (
                
                SELECT
                 
                 JSON_AGG
                (
                    JSONB_BUILD_OBJECT
                        (
                            'field', avito_board_mapper.product_field,
                            'element', avito_board_mapper.element,
                            'default', avito_board_mapper.def
                        )
                ) AS avito_board_mapper

               
                FROM (
                    SELECT DISTINCT 
                        avito_mapper.element, 
                        avito_mapper.product_field, 
                        avito_mapper.def
                    FROM 
                        avito_board_mapper_element avito_mapper
                    WHERE 
                        avito_mapper.event = avito_board.event
                        
                        
                        
                      
                ) avito_board_mapper
            )",
                'avito_board_mapper',
                'true');


        /** @var CategoryProductSectionFieldUid */


        $dbal
            ->addSelect('product_property.product_property')
            ->leftJoin(
                'cteSelect',
                "LATERAL (
                
                SELECT
                 
                 JSON_AGG
                (
                    JSONB_BUILD_OBJECT
                        (
                            'field', product_property.field,
                            'type', product_property.type,
                            'value', product_property.value
                        )
                ) AS product_property

                FROM (
                    SELECT DISTINCT 
                    
                        product_property.field,
                        product_property.value,
                        category_section_field.type
                    
                    FROM product_property
                    
                   JOIN product_category_section category_section
                   ON category_section.event = cteSelect.category_product_event

                   
                    JOIN product_category_section_field category_section_field
                   ON category_section_field.section = category_section.id
                    AND category_section_field.const = product_property.field

                    
                    WHERE 
                    
                    product_property.event = product.event
                      
                ) product_property
            )",
                'product_property',
                'true');


        //$dbal->allGroupByExclude();
        //exit($dbal->getSQL()); /* TODO: удалить !!! */
        //dd($dbal->analyze()); /* TODO: удалить !!! */
        //dd($dbal->fetchAllAssociative()); /* TODO: удалить !!! */


        //$dbal->allGroupByExclude();

        $dbal->andWhere('(avito_board.id IS NOT NULL AND avito_board_event.category IS NOT NULL)');


        //$dbal->allGroupByExclude();
        //exit($dbal->getSQL()); /* TODO: удалить !!! */
        //dd($dbal->analyze()); /* TODO: удалить !!! */
        //dd($dbal->fetchAllAssociative()); /* TODO: удалить !!! */


        //        /** Только заказы, у которых указана стоимость */
        //        $dbal->andWhere('
        //            (
        //                CASE
        //                   WHEN product_modification_price.price IS NOT NULL AND product_modification_price.price > 0
        //                   THEN product_modification_price.price
        //
        //                   WHEN product_variation_price.price IS NOT NULL AND product_variation_price.price > 0
        //                   THEN product_variation_price.price
        //
        //                   WHEN product_offer_price.price IS NOT NULL AND product_offer_price.price > 0
        //                   THEN product_offer_price.price
        //
        //                   WHEN product_price.price IS NOT NULL AND product_price.price > 0
        //                   THEN product_price.price
        //
        //                   ELSE 0
        //                END
        //            ) > 0
        //        ');


        //        /** Получаем значение из СВОЙСТВ товара */
        //        $dbal
        //            ->leftJoin(
        //                'product',
        //                ProductProperty::class,
        //                'product_property',
        //                'product_property.event = product.event',
        //            );


        $dbal->enableCache('avito-board', '1 day');


        $dbal->orderBy('product.event', 'DESC');

        $result = $dbal->fetchAllHydrate(AllProductsWithMapperResult::class);

        return (true === $result->valid()) ? $result : false;
    }
}
