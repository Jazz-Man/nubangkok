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
     * @var array
     */
    protected $_nuPointsToMoneyRates = [];

    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init(\Encomage\Nupoints\Model\ResourceModel\Nupoints::class);
        $this->_nuPointsToMoneyRates[50] = ['from' => 3000, 'to' => 3999];
        $this->_nuPointsToMoneyRates[100] = ['from' => 4000, 'to' => 4999];
        $this->_nuPointsToMoneyRates[150] = ['from' => 5000];
    }

    /**
     * @param $baht
     * @return float|int
     */
    public function getConvertedMoneyToNupoints($baht)
    {
        return (floor($baht / 100)) * 100;
    }

    /**
     * @param null $nuPoints
     * @param bool $returnNupointsForRedeem
     * @return int|string
     */
    public function getConvertedNupointsToMoney($nuPoints = null, $returnNupointsForRedeem = false)
    {
        if ($nuPoints === null) {
            $nuPoints = $this->getNupoints();
        }
        $money = 0;
        $redeem = 0;
        foreach ($this->_nuPointsToMoneyRates as $coast => $rate) {
            if (isset($rate['to'])) {
                if ($nuPoints >= $rate['from'] && $nuPoints <= $rate['to']) {
                    $redeem = $rate['from'];
                    $money = $coast;
                }
            } else {
                if ($nuPoints >= $rate['from']) {
                    $redeem = $rate['from'];
                    $money = $coast;
                }
            }
        }
        return (!$returnNupointsForRedeem) ? $money : $redeem;
    }

    /**
     * @return bool|int
     */
    public function getMinNuPointsCountForRedeem()
    {
        if (count($this->_nuPointsToMoneyRates)) {
            $rates = $this->_nuPointsToMoneyRates;
            $firstRate = array_shift($rates);
            return (int)$firstRate['from'];
        }
        return false;
    }

    /**
     * @return $this
     */
    public function redeemNupoints()
    {
        $convertedToMoney = $this->getConvertedNupointsToMoney();
        if ($convertedToMoney) {
            $redeemed = $this->_nuPointsToMoneyRates[$convertedToMoney];
            if ($this->getNupoints() < $redeemed) {
                //TODO:: Throw exception;
            }
            $this->setNupoints((int)$this->getNupoints() - (int)$redeemed);
        }
        return $this;
    }

    /**
     * @param $value
     * @param bool $isConvert - Transfer baht to nupoints
     * @return mixed
     */
    public function addNupoints($value, $isConvert = false)
    {
        if ($isConvert) {
            $value = $this->getConvertedMoneyToNupoints($value);
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
        return (int)$this->_getData(self::NUPOINTS_VALUE);
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