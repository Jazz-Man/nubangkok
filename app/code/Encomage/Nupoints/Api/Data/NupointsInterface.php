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
     * @param integer $id
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     */
    public function setItemId($id);

    /**
     * @param integer $customerId
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     */
    public function setCustomerId($customerId);

    /**
     * @param $value
     * @return \Encomage\Nupoints\Api\Data\NupointsInterface
     */
    public function setNupoints($value);
}