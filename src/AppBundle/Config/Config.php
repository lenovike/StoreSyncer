<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 04.11.15
 * Time: 21:32
 */
namespace AppBundle\Config;

use AppBundle\Store\SeoShopStore;
use AppBundle\Store\ShopifyStore;

Class Config {

    const STORE_CONFIG_SHOPIFY_SESSION_KEY = 'shopify_config';
    const STORE_CONFIG_SEOSHOP_SESSION_KEY = 'seoshop_config';
    const STORE_CONFIG_SECTION = 'store_config';
    const STORE_TYPE_CONFIG_SECTION = 'store_type';

    static protected $db = [
        self::STORE_CONFIG_SHOPIFY_SESSION_KEY => [
            'store_type' =>'Shopify',
            'store_config' =>[
                'shop_id' => 'leoniddev',
                'api_key' => '0732c0d8dab452dd34254b0acb409cf2',
                'shared_key' => 'b12406226f2ea7763d9fb86f80904eb6',
//                'access_token'=> 'c3533dd9c479a67a546b78da9df9045c',
                'access_token' => ''
            ]
        ],
        self::STORE_CONFIG_SEOSHOP_SESSION_KEY => [
            'store_type' =>'SeoShop',
            'store_config' =>[
                'shop_id' => '106244',
                'api_key' => '828cbe11501bfe303233c78eb74b06fb',
                'api_secret' => '11b94b2c95b6d584fea8c87ae69cff56',
//                'access_token'=> '3caba26e7a1a6679dea555cf0344afda',
                'access_token' => ''
            ]
        ]
    ];

    static protected $configKeys = [
        ShopifyStore::STORE_TYPE => self::STORE_CONFIG_SHOPIFY_SESSION_KEY,
        SeoShopStore::STORE_TYPE => self::STORE_CONFIG_SEOSHOP_SESSION_KEY
    ];

    static public function getConfigKeyByStoreType($storeType)
    {
        $result = null;
        if (isset(self::$configKeys[$storeType])){
            $result = self::$configKeys[$storeType];
        }

        return $result;
    }

    static public function getConfig()
    {
        return self::$db;
    }
}