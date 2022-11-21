<?php

namespace Reach\Payment\Api;

use Reach\Payment\Api\Data\ReachSessionRelationInterface;
use Reach\Payment\Model\ReachSessionRelation as ReachSessionRelationModel;
use Reach\Payment\Model\ResourceModel\ReachSessionRelation\Collection as ReachSessionRelationCollection;

interface ReachSessionRelationRepositoryInterface
{
    public function newModel(): ReachSessionRelationModel;

    public function newCollection(): ReachSessionRelationCollection;

    public function create($salesOrderId, $reachSessionId): ReachSessionRelationModel;

    public function save(ReachSessionRelationInterface $relation): ReachSessionRelationInterface;

    public function getBySessionId(string $reachSessionId): ReachSessionRelationInterface;
}
