<?php

namespace Encomage\Nupoints\Api;

/**
 * Nupoints CRUD interface
 * Interface NupointsRepositoryInterface
 * @package Encomage\Nupoints\Api
 */
interface NupointsRepositoryInterface
{
    /**
     * @param \Encomage\Nupoints\Api\Data\NupointsInterface $redeem
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Encomage\Nupoints\Api\Data\NupointsInterface $redeem);

    /**
     * @param integer $itemId
     * @return mixed
     */
    public function getById($itemId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Encomage\Nupoints\Api\Data\NupointsSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * @param \Encomage\Nupoints\Api\Data\NupointsInterface $redeem
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     */
    public function delete(\Encomage\Nupoints\Api\Data\NupointsInterface $redeem);

    /**
     * @param integer $itemId
     * @return mixed
     */
    public function deleteById($itemId);

    /**
     * @param integer $customerId
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     */
    public function getByCustomerId($customerId);

    /**
     * @param integer $customerId
     * @return mixed
     */
    public function deleteByCustomerId($customerId);

    /**
     * @param mixed $options
     * @param mixed $method
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface mixed
     */
    public function changeNupointsCount($options, $method);

    /**
     * @param string $customerCode
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface mixed
     */
    public function getNupointsByCustomerCode($customerCode);
}