<?php

namespace Reach\Payment\Model;

use Magento\Framework\Model\AbstractModel;
use Reach\Payment\Api\Data\ReachSessionRelationInterface;
use Reach\Payment\Model\ResourceModel\ReachSessionRelation as Resource;

class ReachSessionRelation extends AbstractModel implements ReachSessionRelationInterface
{
    protected function _construct()
    {
        $this->_init(Resource::class);
    }

    /**
     * @return int|void
     */
    public function getSalesOrderId()
    {
        return $this->getData(self::SALES_ORDER_ID);
    }

    /**
     * @param $salesOrderId
     * @return ReachSessionRelation
     */
    public function setSalesOrderId($salesOrderId): ReachSessionRelation
    {
        return $this->setData(self::SALES_ORDER_ID, $salesOrderId);
    }

    /**
     * @return string|void
     */
    public function getReachSessionId()
    {
        return $this->getData(self::REACH_SESSION_ID);
    }

    /**
     * @param $sessionId
     * @return ReachSessionRelation
     */
    public function setReachSessionId($sessionId): ReachSessionRelation
    {
        return $this->setData(self::REACH_SESSION_ID, $sessionId);
    }

    /**
     * @return int|void
     */
    public function getReachOrderId()
    {
        return $this->getData(self::REACH_ORDER_ID);
    }

    /**
     * @param $reachOrderId
     * @return ReachSessionRelation
     */
    public function setReachOrderId($reachOrderId): ReachSessionRelation
    {
        return $this->setData(self::REACH_ORDER_ID, $reachOrderId);
    }
}
