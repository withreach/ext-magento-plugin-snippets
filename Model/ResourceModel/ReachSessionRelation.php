<?php

namespace Reach\Payment\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Reach\Payment\Api\Data\ReachSessionRelationInterface;

/**
 * ReachSessionRelation model
 *
 */
class ReachSessionRelation extends AbstractDb
{
    const TABLE_NAME = 'reach_session';

    protected function _construct()
    {
        $this->_init(self::TABLE_NAME, ReachSessionRelationInterface::ENTITY_ID);
    }
}
