<?php

namespace Reach\Payment\Model;

use Exception;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Reach\Payment\Api\Data\ReachSessionRelationInterface;
use Reach\Payment\Api\ReachSessionRelationRepositoryInterface;
use Reach\Payment\Model\ReachSessionRelation as ReachSessionRelationModel;
use Reach\Payment\Model\ReachSessionRelationFactory as ReachSessionRelationModelFactory;
use Reach\Payment\Model\ResourceModel\ReachSessionRelation as ReachSessionRelationResource;
use Reach\Payment\Model\ResourceModel\ReachSessionRelation\Collection as ReachSessionRelationCollection;
use Reach\Payment\Model\ResourceModel\ReachSessionRelation\CollectionFactory as ReachSessionRelationCollectionFactory;

class ReachSessionRelationRepository implements ReachSessionRelationRepositoryInterface
{
    protected ReachSessionRelationResource $resource;

    protected ReachSessionRelationModelFactory $reachSessionRelationModelFactory;

    protected ReachSessionRelationCollectionFactory $reachSessionRelationCollectionFactory;

    public function __construct(
        ReachSessionRelationResource $resource,
        ReachSessionRelationModelFactory $reachSessionRelationModelFactory,
        ReachSessionRelationCollectionFactory $reachSessionRelationCollectionFactory
    ){
        $this->resource = $resource;
        $this->reachSessionRelationModelFactory = $reachSessionRelationModelFactory;
        $this->reachSessionRelationCollectionFactory = $reachSessionRelationCollectionFactory;
    }

    public function newModel(): ReachSessionRelationModel
    {
        return $this->reachSessionRelationModelFactory->create();
    }

    public function newCollection(): ReachSessionRelationCollection
    {
        return $this->reachSessionRelationCollectionFactory->create();
    }

    public function create($salesOrderId, $reachSessionId): ReachSessionRelationModel
    {
        $reachSessionRelationModel = $this->newModel()
            ->setSalesOrderId($salesOrderId)
            ->setReachSessionId($reachSessionId);

        $this->save($reachSessionRelationModel);

        return $reachSessionRelationModel;
    }

    public function save(ReachSessionRelationInterface $relation): ReachSessionRelationInterface
    {
        try {
            $this->resource->save($relation);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }

        return $relation;
    }

    public function getBySessionId(string $reachSessionId): ReachSessionRelationInterface
    {
        $reachSession = $this->reachSessionRelationModelFactory->create();
        $this->resource->load($reachSession, $reachSessionId, ReachSessionRelationInterface::REACH_SESSION_ID);

        if (!$reachSession->getId()) {
            throw new NoSuchEntityException(__('Unable to find Reach session with ID "%1"', $reachSessionId));
        }

        return $reachSession;
    }
}
