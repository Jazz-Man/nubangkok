<?php

namespace Encomage\Nupoints\Api\Data;

/**
 * Interface NupointsInterface
 * @package Encomage\Nupoints\Api\Data
 */
interface NupointsInterface
{
    const ITEM_ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const NUPOINTS_VALUE  = 'nupoints';

    /**
     * @return int
     */
    public function getItemId();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @return int
     */
    public function getNupoints();

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
     * @param $value
     * @return mixed
     */
    public function setNupoints($value);
}