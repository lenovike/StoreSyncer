<?php
/**
 * Created by PhpStorm.
 * User: leonid
 * Date: 02.11.15
 * Time: 23:10
 */

namespace AppBundle\Service;


use AppBundle\Store\StoreFactory;

class Syncer  {

    /** @var \AppBundle\Service\SyncProcessor  */
    protected $syncProcessor;

    public function __construct(SyncProcessor $syncProcessor)
    {
        $this->syncProcessor = $syncProcessor;
    }

    /**
     * Sync Data
     *
     * @param array $fromStoreConfig
     * @param array $toStoreConfig
     * @param bool $updateExists
     * @param array $entityTypes
     * @return array
     */
    public function syncData($fromStoreConfig, $toStoreConfig, $updateExists = false, $entityTypes = [])
    {
        $storeFrom = StoreFactory::createStore($fromStoreConfig);
        $storeTo = StoreFactory::createStore($toStoreConfig);
        $this->syncProcessor->setStoreFrom($storeFrom);
        $this->syncProcessor->setStoreTo($storeTo);
        $result = [];
        if (empty($entityTypes)){
            $result = $this->syncProcessor->syncAllData($updateExists);
        } else {
            foreach ($entityTypes as $entityType){
                $result[$entityType] = $this->syncProcessor->sync($entityType,$updateExists);
            }
        }
        return $result;
    }
}