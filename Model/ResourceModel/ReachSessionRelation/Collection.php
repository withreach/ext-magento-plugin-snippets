<?php

namespace Reach\Payment\Model\ResourceModel\ReachSessionRelation;

use Magento\Cms\Model\ResourceModel\AbstractCollection;
use Magento\Store\Model\Store;
use Reach\Payment\Model\ReachSessionRelation as ReachSessionRelationModel;
use Reach\Payment\Model\ResourceModel\ReachSessionRelation as ReachSessionRelationResource;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            ReachSessionRelationModel::class,
            ReachSessionRelationResource::class

        );
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
            $this->setFlag('store_filter_added', true);
        }

        return $this;
    }
}