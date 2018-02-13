<?php

namespace Encomage\Nupoints\Model;

use Encomage\Nupoints\Api\NupointsRepositoryInterface;
use Encomage\Nupoints\Api\Data\NupointsInterface;
use Encomage\Nupoints\Model\ResourceModel\Nupoints as ResourceNupoints;
use Encomage\Nupoints\Model\NupointsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class NupointsRepository
 * @package Encomage\Nupoints\Model
 */
class NupointsRepository implements NupointsRepositoryInterface
{
    /**
     * @var ResourceNupoints
     */
    private $resource;

    /**
     * @var \Encomage\Nupoints\Model\NupointsFactory
     */
    private $nupointsFactory;

    /**
     * NupointsRepository constructor.
     * @param ResourceNupoints $resourceRedeem
     * @param \Encomage\Nupoints\Model\NupointsFactory $nupointsFactory
     */
    public function __construct(
        ResourceNupoints $resourceRedeem,
        NupointsFactory $nupointsFactory
    )
    {
        $this->resource = $resourceRedeem;
        $this->nupointsFactory = $nupointsFactory;
    }

    /**
     * @param NupointsInterface $nupoint
     * @return NupointsInterface|mixed
     * @throws CouldNotSaveException
     */
    public function save(NupointsInterface $nupoint)
    {
        try {
            $this->resource->save($nupoint);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $nupoint;
    }

    /**
     * @param $itemId
     * @return Nupoints|mixed
     * @throws NoSuchEntityException
     */
    public function getById($itemId)
    {
        $nupoint = $this->nupointsFactory->create();
        $this->resource->load($nupoint, $itemId);
        if (!$nupoint->getId()) {
            throw new NoSuchEntityException(__('Item id "%1" does not exist.', $itemId));
        }
        return $nupoint;
    }

    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        // TODO: Implement getList() method.
    }

    /**
     * @param NupointsInterface $nupoint
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(NupointsInterface $nupoint)
    {
        try {
            $this->resource->delete($nupoint);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $itemId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($itemId)
    {
        return $this->delete($this->getById($itemId));
    }

    /**
     * @param $customerId
     * @return Nupoints|mixed
     * @throws NoSuchEntityException
     */
    public function getByCustomerId($customerId)
    {
        $nupoint = $this->nupointsFactory->create();
        $this->resource->load($nupoint, $customerId, 'customer_id');
        if (!$nupoint->getId()) {
            throw new NoSuchEntityException(__('Item for customer does not exist.'));
        }
        return $nupoint;
    }

    /**
     * @param $customerId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteByCustomerId($customerId)
    {
        return $this->delete($this->getByCustomerId($customerId));
    }
}