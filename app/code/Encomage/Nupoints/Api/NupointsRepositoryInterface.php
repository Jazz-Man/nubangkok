<?php

namespace Encomage\Nupoints\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Nupoints CRUD interface
 * Interface NupointsRepositoryInterface
 * @package Encomage\Nupoints\Api
 */
interface NupointsRepositoryInterface
{

    /**
     * @param Data\NupointsInterface $redeem
     * @return mixed
     */
    public function save(Data\NupointsInterface $redeem);

    /**
     * @param $itemId
     * @return mixed
     */
    public function getById($itemId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param Data\NupointsInterface $redeem
     * @return mixed
     */
    public function delete(Data\NupointsInterface $redeem);

    /**
     * @param $itemId
     * @return mixed
     */
    public function deleteById($itemId);

    /**
     * @param $customerId
     * @return mixed
     */
    public function getByCustomerId($customerId);

    /**
     * @param $customerId
     * @return mixed
     */
    public function deleteByCustomerId($customerId);
}