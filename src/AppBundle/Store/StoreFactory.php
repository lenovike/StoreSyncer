<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 02.11.15
 * Time: 23:10
 */

namespace AppBundle\Store;

use AppBundle\Config\Config;

class StoreFactory  {

    /**
     * Create Store
     *
     * @param $storeConfig
     * @return mixed
     */
    static public function createStore($storeConfig){
        $store = null;
        $storeType = $storeConfig[Config::STORE_TYPE_CONFIG_SECTION];

        switch ($storeType){
            case ShopifyStore::STORE_TYPE:
                $store = new ShopifyStore($storeConfig[Config::STORE_CONFIG_SECTION]);
                break;
            case SeoShopStore::STORE_TYPE:
                $store = new SeoShopStore($storeConfig[Config::STORE_CONFIG_SECTION]);
                break;

        }
        return $store;
    }
} 