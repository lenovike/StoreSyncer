<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EntityMapping
 *
 * @ORM\Table(name="entity_mapping")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\EntityMappingRepository")
 */
class EntityMapping
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="entity_type", type="string", length=200)
     */
    private $entityType;
    /**
     * @var string
     *
     * @ORM\Column(name="source_shop_id", type="string", length=200)
     */
    private $sourceShopId;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_shop_id", type="string", length=200)
     */
    private $destinationShopId;

    /**
     * @var string
     *
     * @ORM\Column(name="source_entity_id", type="string")
     */
    private $sourceEntityId;

    /**
     * @var string
     *
     * @ORM\Column(name="destination_entity_id", type="string")
     */
    private $destinationEntityId;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set entityType
     *
     * @param string $entityType
     * @return $this
     */
    public function setEntityType($entityType)
    {
        $this->entityType = $entityType;

        return $this;
    }

    /**
     * Get Entity Type
     *
     * @return string
     */
    public function getEntityType()
    {
        return $this->entityType;
    }

    /**
     * Set Source Shop id
     *
     * @param string $sourceShopId
     * @return $this
     */
    public function setSrouceShopId($sourceShopId)
    {
        $this->sourceShopId = $sourceShopId;

        return $this;
    }

    /**
     * Get Source Shop Id
     *
     * @return string
     */
    public function getSourceShopId()
    {
        return $this->sourceShopId;
    }

    /**
     * Set Destination Shop Id
     *
     * @param string $destinationShopId
     * @return $this
     */
    public function setDestinationShopId($destinationShopId)
    {
        $this->destinationShopId = $destinationShopId;

        return $this;
    }

    /**
     * Get Destination Shop Id
     *
     * @return string
     */
    public function getDestinationShopId()
    {
        return $this->destinationShopId;
    }

    /**
     * Set Source Entity Id
     *
     * @param int $sourceEntityId
     *
     * @return $this
     */
    public function setSourceEntityId($sourceEntityId)
    {
        $this->sourceEntityId = $sourceEntityId;

        return $this;
    }

    /**
     * Get Source Entity Id
     *
     * @return string
     */
    public function getSourceEntityId()
    {
        return $this->sourceEntityId;
    }

    /**
     * Set Destination Entity Id
     *
     * @param int $destinationEntityId
     * @return $this
     */
    public function setDestinationEntityId($destinationEntityId)
    {
        $this->destinationEntityId = $destinationEntityId;

        return $this;
    }

    /**
     * Get Destination Entity Id
     *
     * @return int
     */
    public function getDestinationEntityId()
    {
        return $this->destinationEntityId;
    }



}
