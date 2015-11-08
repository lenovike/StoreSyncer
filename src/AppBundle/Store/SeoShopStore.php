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
use AppBundle\Oauth2\Provider\ShopifyProvider;
use Symfony\Component\Routing\Router;

class SeoShopStore extends  AbstractStore{

    const STORE_TYPE = 'SeoShop';
    const CONFIG_SHOP_ID = 'shop_id';
    const CONFIG_API_KEY = 'api_key';
    const CONFIG_API_SECRET = 'api_secret';
    const CONFIG_ACCESS_TOKEN = 'access_token';
    const CONFIG_CALLBACK_URL = 'callback_url';

    const SERVER_TYPE = 'live';
    const API_LANG = 'nl';

    protected $configParams = [
        self::CONFIG_API_KEY,
        self::CONFIG_API_SECRET,
        self::CONFIG_ACCESS_TOKEN
    ];


    protected $callbackUrl;

    protected $mapping;

    protected $shopId;

    /** @var \WebshopappApiClient  */
    protected $apiClient;





    public function __construct ($storeConfig){
        $this->checkConfig($storeConfig);
        $this->shopId = $storeConfig[self::CONFIG_SHOP_ID];
        $secret = md5($storeConfig[self::CONFIG_ACCESS_TOKEN].$storeConfig[self::CONFIG_API_SECRET]);
        $this->apiClient = new \WebshopappApiClient(
            self::SERVER_TYPE,
            $storeConfig[self::CONFIG_API_KEY],
            $secret,
            self::API_LANG
        );
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
            $this->productsCount = $this->apiClient->products->count();
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
            $this->categoriesCount = $this->apiClient->categories->count();
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
     * @return bool|mixed
     */
    public function processChunk($entityType, $items, $updateExists = false)
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
     *
     * @return int
     */
    public function handleProducts($items, $updateExists = false)
    {
        $countHandled = 0;
        /** @var Product $item */
        foreach ($items as $item){
            $productRequestItem = [];
            $productRequestItem['title'] = $item->getTitle();

            $existProduct = $this->getExistProduct($item);
            $updateExists = $updateExists && $existProduct;
            if ($updateExists){
                $product = $this->apiClient->products->update($existProduct['id'], $productRequestItem);
            } else {
                $product = $this->apiClient->products->create($productRequestItem);
            }


            if ($product){
                $productId = $product['id'];
                $this->handleProductImages($item,$updateExists,$productId);
                $this->handleProductCategories($item,$updateExists,$productId);
                $this->mapping[AbstractStore::ENTITY_TYPE_PRODUCT][$item->getId()] = $productId;
                $countHandled++;
            }
        }

        return $countHandled;
    }

    protected function getExistProduct($item)
    {
        $product = null;
        $existProductId = isset($this->mapping[AbstractStore::ENTITY_TYPE_PRODUCT][$item->getId()]) ?
            $this->mapping[AbstractStore::ENTITY_TYPE_PRODUCT][$item->getId()] :
            null;
        if ($existProductId){
            $product = $this->apiClient->products->get($existProductId);
        }

        return $product;
    }

    protected function handleProductImages(Product $item, $updateExists, $productId)
    {
        if ($updateExists){
            $currentImages = $this->apiClient->productsImages->get($productId);
            if (!empty($currentImages)){
                foreach ($currentImages as $curImage){
                    $this->apiClient->productsImages->delete($productId,$curImage['id']);
                }
            }
        }
        if ($item->getMainImage()){
            $imageRequestItem = $this->prepareCreateImageRequest($item->getMainImage());
            $this->apiClient->productsImages->create($productId,$imageRequestItem);
        }
        foreach($item->getAdditionalImages() as $image){
            $imageRequestItem = $this->prepareCreateImageRequest($image);
            $this->apiClient->productsImages->create($productId,$imageRequestItem);
        }

        return $this;
    }

    protected function prepareCreateImageRequest($image)
    {
        $imageRequestItem = [];

        $urlPath = parse_url($image,PHP_URL_PATH);
        $type = pathinfo($urlPath, PATHINFO_EXTENSION);
        $name = pathinfo($urlPath, PATHINFO_FILENAME);
        $imageRequestItem['filename'] = $name.".$type";
        $data = file_get_contents($image);
        $imageRequestItem['attachment'] =base64_encode($data);
        return $imageRequestItem;
    }

    protected function handleProductCategories(Product $item, $updateExists, $productId)
    {
        $categoryIds = $item->getCategoryIds();
        if ( !empty($categoryIds)){
            if ($updateExists){
                $categoryLinks = $this->apiClient->categoriesProducts->get(null,['product'=>$productId]);
                if (!empty($categoryLinks)){
                    foreach ($categoryLinks as $categoryLink){
                        $this->apiClient->categoriesProducts->delete($categoryLink['id']);
                    }
                }
            }
            foreach ($categoryIds as $catId){
                if (isset($this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$catId])){
                    $categoryProductRequestItem = [];
                    $catId = $this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$catId];
                    $categoryProductRequestItem['category'] = $catId;
                    $categoryProductRequestItem['product'] = $productId;
                    $linkResult = $this->apiClient->categoriesProducts->create($categoryProductRequestItem);
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
     *
     * @return int
     */
    public function handleCategories($items, $updateExists)
    {
        $countHandled = 0;
        /** @var Category $item */
        foreach ($items as $item){
            $catRequestItem = [];
            $catRequestItem['title'] = $item->getTitle();
            // some default paramteres
            $catRequestItem['type']  = 'category';
            $catRequestItem['depth'] = 1;
            $catRequestItem['isVisible'] = false;
            $existCatId = isset($this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$item->getId()]) ?
                $this->mapping[AbstractStore::ENTITY_TYPE_CATEGORY][$item->getId()] :
                null;
            $updateExists = $updateExists && $existCatId;
            if ($updateExists){
                $category = $this->apiClient->categories->update($existCatId, $catRequestItem);
            } else {
                $category = $this->apiClient->categories->create($catRequestItem);
            }


            if ($category){
                $countHandled++;
                $catId = $category['id'];
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
        $categoriesData = $this->apiClient->categories->get(null, $params);
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
        $productsData = $this->apiClient->products->get(null, $params);
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
         $images = $this->apiClient->productsImages->get($product['id']);
         if (!empty($images)){
             foreach ($images as $item){
                 $image = $item['src'];
                 if ($item['sortOrder'] == 1){
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
        $categoryLinks = $this->apiClient->categoriesProducts->get(null,['product'=>$product['id']]);
        if (!empty($categoryLinks)){
            foreach ($categoryLinks as $categoryLink){
                $productContainer->addCategoryId($categoryLink['category']['resource']['id']);
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

    /**
     * Get Shop Id
     *
     * @return mixed
     */
    public  function getShopId()
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