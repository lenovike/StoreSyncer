<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 02.11.15
 * Time: 23:10
 */

namespace AppBundle\Store;


use AppBundle\Client\Shopify;
use AppBundle\Container\Category;
use AppBundle\Container\Product;
use Symfony\Component\Routing\Router;

class ShopifyStore extends  AbstractStore{

    const STORE_TYPE = 'Shopify';
    const CONFIG_SHOP_ID = 'shop_id';
    const CONFIG_API_KEY = 'api_key';
    const CONFIG_SHARED_KEY = 'shared_key';
    const CONFIG_ACCESS_TOKEN = 'access_token';
    const CONFIG_CALLBACK_URL = 'callback_url';

    protected $configParams = [
        self::CONFIG_SHOP_ID,
        self::CONFIG_ACCESS_TOKEN,

    ];

    protected $shopId;

    protected $callbackUrl;

    protected $shopifyClient;





    public function __construct ($storeConfig){
        $this->checkConfig($storeConfig);
        $this->shopId = $storeConfig[self::CONFIG_SHOP_ID];
        $this->shopifyClient = new Shopify($storeConfig[self::CONFIG_SHOP_ID], $storeConfig[self::CONFIG_ACCESS_TOKEN]);
    }


    /**
     * Check Config
     *
     * @param $storeConfig
     * @return $this
     * @throws \Exception
     */
    public function checkConfig($storeConfig){
        foreach ($this->configParams as $param){
            if (!isset($storeConfig[$param])){
                throw new \Exception("Please spicefy correct config for the store. Field ".$param." is unedfined");
            }
        }
        return $this;
    }

    /**
     * Get Products Count
     *
     * @return int
     */
    public function getProductsCount()
    {
        if (!$this->productsCount){
            $this->productsCount = $this->shopifyClient->getProductsCount();
        }
        return $this->productsCount;
    }

    /**
     * Get Categories Count
     *
     * @return int
     */
    public function getCategoriesCount()
    {
        if (!$this->categoriesCount){
            $this->categoriesCount = $this->shopifyClient->getCategoriesCount();
        }
        return $this->categoriesCount;
    }

    /**
     * Get Chunk
     *
     * @param $entityType
     * @param $chunkPage
     * @param $chunkSize
     * @return array
     */
    public function getChunk($entityType, $chunkPage, $chunkSize)
    {
        $items = [];

        switch($entityType){
            case self::ENTITY_TYPE_CATEGORY:
                $items = $this->getCategories($chunkPage,$chunkSize);
                break;
            case self::ENTITY_TYPE_PRODUCT:
                $items = $this->getProducts($chunkPage,$chunkSize);
                break;
        }

        return $items;
    }

    /**
     * Process Chunk
     *
     * @param $entityType
     * @param $items
     * @param $updateExists
     *
     * @return bool|int
     */
    public function processChunk($entityType, $items, $updateExists)
    {
        $result = false;
        switch($entityType){
            case self::ENTITY_TYPE_CATEGORY:
                $result = $this->handleCategories($items, $updateExists);
                break;
            case self::ENTITY_TYPE_PRODUCT:
                $result = $this->handleProducts($items, $updateExists);
                break;
        }

        return $result;
    }

    /**
     * Handle Products
     *
     * @param $items
     * @param $updateExists
     * @return int
     */
    public function handleProducts($items, $updateExists)
    {
        $countHandled = 0;
        /** @var Product $item */
        foreach ($items as $item){
            $product = null;
            $existProduct = $this->getExistProduct($item);
            $updateExists = $updateExists && $existProduct ;
            $productRequestItem = [];
            $productRequestItem['product']['title'] = $item->getTitle();
            $productRequestItem['product']['images'][] = ['src'=>$item->getMainImage()];
            foreach($item->getAdditionalImages() as $image){
                $productRequestItem['product']['images'][] = ['src'=>$image];
            }

            if ($updateExists  ){
                $productRequestItem['product']['id'] = $existProduct['id'];
                $product = $this->shopifyClient->updateProduct($existProduct['id'], $productRequestItem);
            } else {
                $product = $this->shopifyClient->createProduct($productRequestItem);
                $this->mapping[AbstractStore::ENTITY_TYPE_PRODUCT][$item->getId()] = $product['id'];
            }

            if ($product){
                $countHandled++;
                $productId = $product['id'];
                $this->handleProductCategories($item, $productId, $updateExists);


            }
        }

        return $countHandled;
    }

    /**
     * Get Exist
     *
     * @param $item
     * @return array|null
     */
    protected function getExistProduct($item)
    {
        $product = null;
        $existProductId = isset($this->mapping[AbstractStore::ENTITY_TYPE_PRODUCT][$item->getId()]) ?
            $this->mapping[AbstractStore::ENTITY_TYPE_PRODUCT][$item->getId()] :
            null;
        if ($existProductId){
            $product = $this->shopifyClient->getProduct($existProductId);
        }

        return $product;

    }

    /**
     * Handle Products Categories
     *
     * @param Product $item
     * @param $productId
     * @param $updateExists
     * @return $this
     */
    protected function handleProductCategories(Product $item, $productId, $updateExists)
    {
        $categoryIds = $item->getCategoryIds();
        if (!empty($product) && !empty($categoryIds)){
            if ($updateExists){
                //clear current and sync new state
                $collects = $this->shopifyClient->getCollects(['product_id'=>$productId]);
                if (!empty($collects)){
                    foreach ($collects as $collect){
                        $this->shopifyClient->deleteCollect($collect['id']);
                    }
                }
            }
            foreach ($categoryIds as $catId){
                if (isset($this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$catId])){
                    $mappedCategoryId = $this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$catId];
                    $catRequestItem['collect']['product_id'] = $productId;
                    $catRequestItem['collect']['collection_id'] = $mappedCategoryId;
                    $linkResult = $this->shopifyClient->createCollect($catRequestItem);
                    if (empty($linkResult)){
                        //log no link created
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Handle Categories
     *
     * @param $items
     * @param $updateExists
     * @return int
     */
    public function handleCategories($items, $updateExists=false)
    {
        $countHandled = 0;
        /** @var Category $item */
        foreach ($items as $item){
            $catRequestItem = [];
            $catRequestItem['custom_collection']['title'] = $item->getTitle();
            $existCatId = isset($this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$item->getId()]) ?
                $this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$item->getId()] :
                null;
            $updateExists = $updateExists && $existCatId;
            if ($updateExists ){
                $customCollection = $this->shopifyClient->updateCustomCollection($existCatId,$catRequestItem);
            } else {
                $customCollection = $this->shopifyClient->createCustomCollection($catRequestItem);

            }

            if ($customCollection){
                $countHandled++;
                $catId = $customCollection['id'];
                $this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$item->getId()] = $catId;
            }
        }

        return $countHandled;
    }

    /**
     * Get Categories
     *
     * @param $chunk
     * @param $chunkSize
     * @return array
     */
    public function getCategories($chunk,$chunkSize)
    {
        $items = [];

        $params = ['page'=>$chunk,'limit'=>$chunkSize];
        $categoriesData = $this->shopifyClient->getCustomCollections($params);
        if (!empty($categoriesData)){
            foreach ($categoriesData as $category){
                $categoryContainer = new Category();
                $categoryContainer->setId($category['id']);
                $categoryContainer->setTitle($category['title']);
                $items[] =$categoryContainer;
            }
        }

        return $items;
    }

    /**
     * Get Products
     *
     * @param $chunk
     * @param $chunkSize
     * @return array
     */
    public function getProducts($chunk, $chunkSize)
    {
        $items = [];

        $params = ['page'=>$chunk,'limit'=>$chunkSize];
        $productsData = $this->shopifyClient->getProducts($params);
        if (!empty($productsData)){
            foreach ($productsData as $product){
                $productContainer = new Product();
                $productContainer->setId($product['id']);
                $productContainer->setTitle($product['title']);
                $this->addImages($productContainer,$product);
                $this->addCategoryToProduct($productContainer,$product);
                $items[] = $productContainer;
            }
        }


        return $items;
    }

    /**
     * Add Images
     *
     * @param Product $productContainer
     * @param $product
     * @return $this
     */
    public function addImages(Product $productContainer, $product)
    {
         $images = $this->shopifyClient->getProductImages($product['id']);
         if (!empty($images)){
             foreach ($images as $item){
                 $image = $item['src'];
                 if ($item['position'] == 1){
                     $productContainer->setMainImage($image);
                 } else {
                     $productContainer->addAdditionalImage($image);
                 }
             }
         }

        return $this;
    }

    /**
     * Add Category To Product
     *
     * @param Product $productContainer
     * @param $product
     * @return $this
     */
    public function addCategoryToProduct(Product $productContainer, $product)
    {
        $customCollections = $this->shopifyClient->getCustomCollections(['product_id'=>$product['id']]);
        if (!empty($customCollections)){
            foreach ($customCollections as $collection){
                $productContainer->addCategoryId($collection['id']);
            }
        }

        return $this;
    }

    /**
     * Get Entities Count
     *
     * @param $entityType
     * @return bool|int
     */
    public function getEntitiesCount($entityType)
    {
        $result = false;
        switch($entityType){
            case self::ENTITY_TYPE_CATEGORY:
                $result = $this->getCategoriesCount();
                break;
            case self::ENTITY_TYPE_PRODUCT:
                $result = $this->getProductsCount();
                break;
        }

        return $result;
    }

    public function getShopId()
    {
        return $this->shopId;
    }
    /**
     * Get Supported Entities
     *
     * @return array
     */
    public function getSupportedEntities()
    {
        return [self::ENTITY_TYPE_PRODUCT, self::ENTITY_TYPE_CATEGORY];
    }



} 