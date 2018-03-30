<?php

namespace Encomage\Nupoints\Model;

use Encomage\Nupoints\Api\Data\NupointsInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;


/**
 * Class Nupoints
 * @package Encomage\Nupoints\Model
 */
class Nupoints extends AbstractModel implements NupointsInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * Nupoints constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CheckoutSession $checkoutSession
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CheckoutSession $checkoutSession,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->checkoutSession = $checkoutSession;
        $this->_eventPrefix = 'nupoints';
        $this->_eventObject = 'nupoints';
    }

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
        $this->_nuPointsToMoneyRates[150] = ['from' => 5000, 'to' => 11999];
        $this->_nuPointsToMoneyRates[500] = ['from' => 12000];
    }

    /**
     * @param int $nupoints
     * @return $this
     */
    public function enableUseNupointsOnCheckout(int $nupoints)
    {
        $this->setNupointsCheckoutData(
            new DataObject(
                [
                    'nupoints_to_redeem' => $nupoints,
                    'money_to_redeem' => $this->getConvertedNupointsToMoney($nupoints)
                ]
            )
        );
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_enable_use_nupoints_on_checkout_before',
            [$this->_eventObject => $this]
        );
        $this->checkoutSession->setUseCustomerNuPoints($this->getNupointsCheckoutData());
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_enable_use_nupoints_on_checkout_after',
            [$this->_eventObject => $this]
        );
        return $this;
    }

    /**
     * @return $this
     */
    public function disableUseNupointsOnCheckout()
    {
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_disable_use_nupoints_on_checkout_before',
            [$this->_eventObject => $this]
        );
        $this->checkoutSession->setUseCustomerNuPoints(false);
        $this->_eventManager->dispatch(
            $this->_eventPrefix . '_disable_use_nupoints_on_checkout_after',
            [$this->_eventObject => $this]
        );
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCustomerNupointsCheckoutData()
    {
        return $this->checkoutSession->getUseCustomerNuPoints();
    }

    /**
     * @return int
     */
    public function getAvailableNupoints()
    {
        if (!$this->getCustomerNupointsCheckoutData()) {
            return $this->getNupoints();
        }
        return $this->getNupoints() - $this->getCustomerNupointsCheckoutData()->getNupointsToRedeem();
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
     * @return bool
     */
    public function isCanCustomerRedeem()
    {
        return (bool)!$this->getCustomerNupointsCheckoutData()
            && $this->getNupoints() >= $this->getMinNuPointsCountForRedeem();
    }

    /**
     * @return array
     */
    public function getNupointsToMoneyRates()
    {
        return $this->_nuPointsToMoneyRates;
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
     * @throws LocalizedException
     */
    public function redeemNupointsAfterOrderPlaced()
    {
        if ($this->checkoutSession->getUseCustomerNuPoints()) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_redeem_nupoints_after_order_place_before',
                [$this->_eventObject => $this]
            );
            $convertedToMoney = $this->getConvertedNupointsToMoney();
            $redeemed = $this->_nuPointsToMoneyRates[$convertedToMoney]['from'];
            if ($this->getNupoints() < $redeemed) {
                throw new LocalizedException(__('Not enough nupoints for redeem'));
            }
            $this->setNupoints((int)$this->getNupoints() - (int)$redeemed);
            $this->disableUseNupointsOnCheckout();
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_redeem_nupoints_after_order_place_after',
                [$this->_eventObject => $this]
            );
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
        $this->setData('value', $value);
        $this->setData('is_convert', $isConvert);
        if ($this->getData('is_convert')) {
            $value = $this->getConvertedMoneyToNupoints($this->getData('value'));
        }
        $this->setNupoints((int)$this->getNupoints() + (int)$value);
        $this->_eventManager->dispatch($this->_eventPrefix . '_add_nupoints_after', [$this->_eventObject => $this]);
        return $this;
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