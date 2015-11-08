<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 02.11.15
 * Time: 23:10
 */

namespace AppBundle\Store;

abstract class AbstractStore  {

    const ENTITY_TYPE_PRODUCT = 'product';
    const ENTITY_TYPE_CATEGORY = 'category';



    protected $productsCount;

    protected $categoriesCount;

    protected $mapping = [];

    abstract public function getChunk($type, $page, $chunkSize);

    abstract public function processChunk($type, $items, $updateExists);

    abstract public function getProductsCount();

    abstract public function getCategoriesCount();

    abstract public function checkConfig($config);

    abstract public function getSupportedEntities();

    abstract public function getEntitiesCount($entityType);

    abstract public function getShopId();

    /**
     * Set Mapping
     *
     * @param $entityType
     * @param $mapping
     * @return $this
     */
    public function setMapping($entityType, $mapping)
    {
        $this->mapping[$entityType] = $mapping;
        return  $this;
    }

    /**
     * Get Mapping
     *
     * @param $entityType
     *
     * @return mixed
     */
    public function getMapping($entityType)
    {
        return $this->mapping[$entityType];
    }
} 