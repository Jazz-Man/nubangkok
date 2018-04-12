<?php

namespace Encomage\Stories\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Stories CRUD interface
 * Interface StoriesRepositoryInterface
 * @package Encomage\Stories\Api
 */
interface StoriesRepositoryInterface
{

    /**
     * @param Data\StoriesInterface $stories
     * @return mixed
     */
    public function save(Data\StoriesInterface $stories);

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
     * @param Data\StoriesInterface $stories
     * @return mixed
     */
    public function delete(Data\StoriesInterface $stories);

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