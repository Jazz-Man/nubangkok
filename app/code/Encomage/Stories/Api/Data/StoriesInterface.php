<?php

namespace Encomage\Stories\Api\Data;

/**
 * Interface StoriesInterface
 * @package Encomage\Stories\Api\Data
 */
interface StoriesInterface
{
    const ITEM_ID = 'entity_id';
    const CUSTOMER_ID = 'customer_id';

    /**
     * @return int
     */
    public function getItemId();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param $id
     * @return mixed
     */
    public function setItemId($id);

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId);

    /**
     * @param $story
     * @return mixed
     */
    public function setStory($story);
}