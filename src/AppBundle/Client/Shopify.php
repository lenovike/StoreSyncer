<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 31.10.15
 * Time: 18:55
 */

namespace AppBundle\Client;


use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\Config\Definition\Exception\Exception;

class Shopify {

    /** @var \GuzzleHttp\Client  */
    protected $apiClient;

    protected $token = null;

    protected $apiStoreUrl;

    public function __construct($shopId, $token)
    {
        $this->token  = $token;
        $this->apiStoreUrl = "https://{$shopId}.myshopify.com/admin/";
        $this->shopId = $shopId;
        $this->apiClient = new Client(['headers'=>['X-Shopify-Access-Token'=>$this->token]]);
    }


    /**
     * Get Token
     *
     * @return null|string
     */
    protected function getToken()
    {
        return $this->token;
    }

    /**
     * Get Custom Collections
     *
     * @param array $params
     * @return array
     */
    public function getCustomCollections($params = [])
    {
        $collections = [];
        $url = $this->apiStoreUrl."custom_collections.json";
        $response = $this->getResponse('GET',$url,$params);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['custom_collections'])){
                $collections = $result['custom_collections'];
            }
        }

        return $collections;
    }

    /**
     * Get Collects
     *
     * @param array $params
     * @return array
     */
    public function getCollects($params = [])
    {
        $collects = [];
        $url = $this->apiStoreUrl."collects.json";
        $response = $this->getResponse('GET',$url,$params);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['collects'])){
                $collects = $result['collects'];
            }
        }

        return $collects;
    }

    /**
     * Get Products
     *
     * @param array $params
     * @return array
     */
    public function getProducts($params = [])
    {
        $products = [];
        $url = $this->apiStoreUrl."products.json";
        $response = $this->getResponse('GET',$url,$params);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['products'])){
                $products = $result['products'];
            }
        }

        return $products;
    }

    public function getProduct($id, $params = [])
    {
        $product = [];
        $url = $this->apiStoreUrl."products/{$id}.json";
        $response = $this->getResponse('GET',$url,$params);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['product'])){
                $product = $result['product'];
            }
        }

        return $product;
    }

    /**
     * Get Product Images
     *
     * @param null $productId
     * @return array
     */
    public function getProductImages($productId = null)
    {
        $images = [];
        $url = $this->apiStoreUrl."products/{$productId}/images.json";
        $response = $this->getResponse('GET',$url);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['images'])){
                $images = $result['images'];
            }
        }

        return $images;
    }

    /**
     * Create Custom Collection
     *
     * @param $params
     * @return null|array
     */
    public function createCustomCollection($params)
    {
        $customCollection = null;
        $url = $this->apiStoreUrl."custom_collections.json";
        $response = $this->getResponse('POST',$url,$params);
        if ($response && $response->getStatusCode() == 201){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['custom_collection'])){
                $customCollection = $result['custom_collection'];
            }
        }

        return $customCollection;
    }

    /**
     * Create Collect
     *
     * @param $params
     * @return null|array
     */
    public function createCollect($params)
    {
        $collect = null;
        $url = $this->apiStoreUrl."collects.json";
        $response = $this->getResponse('POST',$url,$params);
        if ($response && $response->getStatusCode() == 201){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['collect'])){
                $collect = $result['collect'];
            }
        }

        return $collect;
    }

    /**
     * Create Product
     *
     * @param $params
     * @return null|array
     */
    public function createProduct($params)
    {
        $product = null;
        $url = $this->apiStoreUrl."products.json";
        $response = $this->getResponse('POST',$url,$params);
        if ($response && $response->getStatusCode() == 201){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['product'])){
                $product = $result['product'];
            }

        }

        return $product;
    }

    /**
     * Update Product
     *
     * @param $id
     * @param $params
     * @return null|array
     */
    public function updateProduct($id, $params)
    {
        $product = null;
        $url = $this->apiStoreUrl."products/{$id}.json";
        $response = $this->getResponse('PUT',$url,$params);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['product'])){
                $product = $result['product'];
            }
        }

        return $product;
    }

    /**
     * Update Custom Collection
     *
     * @param $id
     * @param $params
     * @return null|array
     */
    public function updateCustomCollection($id, $params)
    {
        $customCollection = null;
        $url = $this->apiStoreUrl."custom_collections/{$id}.json";
        $response = $this->getResponse('PUT',$url,$params);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if (isset($result['custom_collection'])){
                $customCollection = $result['custom_collection'];
            }
        }

        return $customCollection;
    }

    /**
     * Delete Custom Collection
     *
     * @param $id
     * @return bool
     */
    public function deleteCustomCollection($id)
    {
        $result = false;
        $url = $this->apiStoreUrl."custom_collections/{$id}.json";
        $response = $this->getResponse('DELETE',$url);
        if ($response && $response->getStatusCode() == 200){
            $result = true;
        }

        return $result;
    }

    /**
     * Delete Collect
     *
     * @param $id
     * @return bool
     */
    public function deleteCollect($id)
    {
        $result = false;
        $url = $this->apiStoreUrl."collects/{$id}.json";
        $response = $this->getResponse('DELETE',$url);
        if ($response && $response->getStatusCode() == 200){
            $result = true;
        }
        return $result;
    }

    /**
     * Delete Product
     *
     * @param $id
     * @return bool
     */
    public function deleteProduct($id)
    {
        $result = false;
        $url = $this->apiStoreUrl."products/{$id}.json";
        $response = $this->getResponse('DELETE',$url);
        if ($response && $response->getStatusCode() == 200){
            $result = true;
        }

        return $result;
    }

    /**
     * Get Product Count
     *
     * @return int
     */
    public function getProductsCount()
    {
        $count = 0;
        $url = $this->apiStoreUrl."products/count.json";
        $response = $this->getResponse('GET',$url);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if(isset($result['count'])){
                $count = $result['count'];
            }
        }
        return $count;
    }

    /**
     * Get Categories Count
     *
     * @return int
     */
    public function getCategoriesCount()
    {
        $count = 0;
        $url = $this->apiStoreUrl."custom_collections/count.json";
        $response = $this->getResponse('GET',$url);
        if ($response && $response->getStatusCode() == 200){
            $result = json_decode($response->getBody()->getContents(),true);
            if(isset($result['count'])){
                $count = $result['count'];
            }
        }
        return $count;
    }


    /**
     * Get Response
     *
     * @param $type
     * @param $url
     * @param array $params
     * @return Response|null
     * @throws \Symfony\Component\Config\Definition\Exception\Exception
     */
    protected function getResponse($type, $url, $params =[])
    {
        $response = null;
        $requestParams = [
            'headers' => ['X-Shopify-Access-Token'=>$this->getToken()]
        ];
        $method = strtolower($type);

        switch($method){
            case 'get':
            case 'delete':
                $requestParams['query']=$params;
                break;
            case 'post':
            case 'put':
                $requestParams['body']=json_encode($params);
                $requestParams['headers']['Content-Type'] = 'application/json; charset=utf-8';
                break;
            default :
                throw new Exception('Api Method does not exists please use get post put delete methods');

        }

        /** @var Response $response */
        $response = $this->apiClient->$method($url,$requestParams);

        return $response;

    }




} 