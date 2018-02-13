<?php

namespace Encomage\Nupoints\Model;

use Encomage\Nupoints\Api\Data\NupointsInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Nupoints
 * @package Encomage\Nupoints\Model
 */
class Nupoints extends AbstractModel implements NupointsInterface
{
    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init(\Encomage\Nupoints\Model\ResourceModel\Nupoints::class);
    }

    /**
     * @param $baht
     * @return float|int
     */
    public function convertMoneyToNupoints($baht)
    {
        return (floor($baht / 100)) * 100;
    }

    /**
     * @param $value
     * @param bool $isConvert - Transfer baht to nupoints
     * @return mixed
     */
    public function addNupoints($value, $isConvert = false)
    {
        if ($isConvert) {
            $value = $this->convertMoneyToNupoints($value);
        }
        return $this->setNupoints((int)$this->getNupoints() + (int)$value);
    }

    /**
     * @return int
     */
    public function getItemId()
    {
        return $this->_getData(self::ITEM_ID);
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @return int
     */
    public function getNupoints()
    {
        return $this->_getData(self::NUPOINTS_VALUE);
    }

    /**
     * @param $customerId
     * @return mixed
     */
    public function setCustomerId($customerId)
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function setItemId($id)
    {
        return $this->setData(self::ITEM_ID, $id);
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setNupoints($value)
    {
        return $this->setData(self::NUPOINTS_VALUE, $value);
    }
}