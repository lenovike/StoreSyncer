<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 02.11.15
 * Time: 21:07
 */

namespace AppBundle\Container;

/**
 * Class Products
 * @package AppBundle\Container
 */
class Product {

    /** @var  int */
    protected $id;
    /** @var  string */
    protected $title;
    /** @var  string */
    protected $mainImage;
    /** @var  array */
    protected $additionalImages = [];
    /** @var array  */
    protected $categoryIds = [];


    /**
     * Set Id
     *
     * @param $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get Id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set Title
     *
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get Title
     *
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Add Image
     *
     * @param $image
     * @return $this
     */
    public function addAdditionalImage($image)
    {
        $this->additionalImages[] = $image;
        return $this;
    }

    /**
     * Get Images
     *
     * @return array
     */
    public function getAdditionalImages()
    {
        return $this->additionalImages;
    }

    /**
     * Set Main Image
     *
     * @param $mainImage
     * @return $this
     */
    public function setMainImage($mainImage)
    {
        $this->mainImage = $mainImage;
        return $this;
    }

    /**
     * Get Main Image
     *
     * @return string
     */
    public function getMainImage()
    {
        return $this->mainImage;
    }

    /**
     * Add Category Id
     *
     * @param $catId
     * @return $this
     */
    public function addCategoryId($catId)
    {
        $this->categoryIds[] = $catId;
        return $this;
    }

    /**
     * Get Category Ids
     *
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }




} 