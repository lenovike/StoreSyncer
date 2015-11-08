<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 02.11.15
 * Time: 23:10
 */

namespace AppBundle\Service;

use AppBundle\Entity\EntityMappingRepository;
use AppBundle\Store\AbstractStore;
use Symfony\Bridge\Doctrine\RegistryInterface;


class SyncProcessor  {


    /** @var  AbstractStore */
    protected $storeFrom;

    /** @var  AbstractStore */
    protected $storeTo;

    protected $chunkSize = 10;

    /** @var  EntityMappingRepository */
    protected $mappingRepo;

    public function __construct(RegistryInterface $doctrine)
    {
        $this->mappingRepo = $doctrine->getRepository("AppBundle:EntityMapping");
    }
    /**
     * Set Store From
     *
     * @param $storeFrom
     * @return $this
     */
    public function setStoreFrom(AbstractStore $storeFrom)
    {
        $this->storeFrom = $storeFrom;
        return $this;
    }

    /**
     * Set Store To
     *
     * @param AbstractStore $storeTo
     * @return $this
     */
    public function setStoreTo(AbstractStore $storeTo)
    {
        $this->storeTo = $storeTo;
        return $this;
    }

    /**
     * Set Chunk Size
     *
     * @param $chunkSize
     * @return $this
     */
    public function setChunkSize($chunkSize)
    {
        $this->chunkSize = $chunkSize;
        return $this;
    }


    /**
     * Sync All Data
     *
     * @return array
     */
    public function syncAllData($updateExists=false)
    {
        $categoriesCount = $this->sync(AbstractStore::ENTITY_TYPE_CATEGORY, $updateExists);
        $productsCount = $this->sync(AbstractStore::ENTITY_TYPE_PRODUCT, $updateExists);
        $result = [
            AbstractStore::ENTITY_TYPE_CATEGORY => $categoriesCount,
            AbstractStore::ENTITY_TYPE_PRODUCT => $productsCount
        ];
        return $result;
    }

    /**
     * Sync
     *
     * @param $entityType
     * @param $updateExists
     * @return int
     */
    public function sync($entityType, $updateExists=false)
    {
        $countSynced = 0;
        if ($this->isAvailEntityToSync($entityType)){
            $countItemsToSync = $this->storeFrom->getEntitiesCount($entityType);
            if ($countItemsToSync > 0){
                $entityMapping = $this->getEntitiesMapping(
                    $entityType,
                    $this->storeFrom->getShopId(),
                    $this->storeTo->getShopId()
                );
                $this->storeTo->setMapping($entityType,$entityMapping);
                $chunksCount = ceil($countItemsToSync/$this->chunkSize);
                for ($chunk=1; $chunk<=$chunksCount; $chunk++){
                    $items = $this->storeFrom->getChunk($entityType,$chunk,$this->chunkSize);
                    $count = $this->storeTo->processChunk($entityType, $items, $updateExists);
                    $countSynced+=$count;
                }
                $this->updateEntitiesMapping(
                    $entityType,
                    $this->storeFrom->getShopId(),
                    $this->storeTo->getShopId(),
                    $this->storeTo->getMapping($entityType)
                );
            }
        }

        return $countSynced;

    }

    /**
     * Get Entities Mapping
     *
     * @param $entityType
     * @param $sourceShopId
     * @param $destinationShopIdId
     * @return array
     */
    protected function  getEntitiesMapping($entityType, $sourceShopId, $destinationShopIdId)
    {
        $mapping = $this->mappingRepo->getMapping($entityType,$sourceShopId,$destinationShopIdId);
        return $mapping;
    }

    /**
     * Update Entities Mapping
     *
     * @param $entityType
     * @param $sourceShopId
     * @param $destinationShopIdId
     * @param $mapping
     * @return $this
     */
    protected function updateEntitiesMapping($entityType, $sourceShopId, $destinationShopIdId, $mapping)
    {
        $this->mappingRepo->updateMapping($entityType, $sourceShopId, $destinationShopIdId, $mapping);
        return $this;
    }


    /**
     * Is Avail Entitty To Sync
     *
     * @param $entity
     * @return bool
     */
    protected function isAvailEntityToSync($entity)
    {
        $entitiesToSync = $this->getSupportedEntitiesToSync();
        if (!in_array($entity, $entitiesToSync)){
            return false;
        }
        return true;
    }

    /**
     * Get Supported Entities To Sync
     *
     * @return array
     */
    protected function getSupportedEntitiesToSync()
    {
        $entitiesFrom = $this->storeFrom->getSupportedEntities();
        $entitiesTo = $this->storeTo->getSupportedEntities();

        return array_intersect($entitiesTo, $entitiesFrom);
    }


} 