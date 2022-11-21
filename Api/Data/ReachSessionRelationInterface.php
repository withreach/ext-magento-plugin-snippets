<?php

namespace Reach\Payment\Api\Data;

interface ReachSessionRelationInterface
{
    const ENTITY_ID = 'entity_id';
    const SALES_ORDER_ID = 'sales_order_id';
    const REACH_SESSION_ID = 'reach_session_id';
    const REACH_ORDER_ID = 'reach_order_id';

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param $id
     * @return $this
     */
    public function setEntityId($id);

    /**
     * @return int
     */
    public function getSalesOrderId();

    /**
     * @param int $salesOrderId
     * @return $this
     */
    public function setSalesOrderId($salesOrderId);

    /**
     * @return string
     */
    public function getReachSessionId();

    /**
     * @param string $sessionId
     * @return $this
     */
    public function setReachSessionId($sessionId);

    /**
     * @return int
     */
    public function getReachOrderId();

    /**
     * @param string $reachOrderId
     * @return $this
     */
    public function setReachOrderId($reachOrderId);
}
