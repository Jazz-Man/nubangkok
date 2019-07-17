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
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;


/**
 * Class Nupoints
 * @package Encomage\Nupoints\Model
 */
class Nupoints extends AbstractModel implements NupointsInterface
{
    const NUPOINT_SETTING_LIST = 'nupoints_settings/nupoints_rates/nupoints_list';

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * Nupoints constructor.
     * @param Context $context
     * @param Registry $registry
     * @param CheckoutSession $checkoutSession
     * @param ScopeConfigInterface $scopeConfig
     * @param Json $json
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        CheckoutSession $checkoutSession,
        ScopeConfigInterface $scopeConfig,
        Json $json,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->checkoutSession = $checkoutSession;
        $this->scopeConfig = $scopeConfig;
        $this->json = $json;
        $this->_eventPrefix = 'nupoints';
        $this->_eventObject = 'nupoints';
        $this->fillNupointsToMoneyRates();
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
        $this->_init(ResourceModel\Nupoints::class);
    }

    /**
     * @param int $nupoints
     * @return $this
     */
    public function enableUseNupointsOnCheckout(int $nupoints)
    {
        $money = $this->getConvertedNupointsToMoney($nupoints);
        $this->setNupointsCheckoutData(
            new DataObject(
                [
                    'nupoints_to_redeem' => $nupoints,
                    'money_to_redeem' => $money,
                    'product' => $this->_nuPointsToMoneyRates[$money]['related_product']
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
        return floor($baht / 100) * 100;
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
            } elseif ($nuPoints >= $rate['from']) {
                $redeem = $rate['from'];
                $money = $coast;
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
        if ($this->getCustomerNupointsCheckoutData()) {
            $this->_eventManager->dispatch(
                $this->_eventPrefix . '_redeem_nupoints_after_order_place_before',
                [$this->_eventObject => $this]
            );

            $convertedToMoney = $this->getConvertedNupointsToMoney(
                $this->getCustomerNupointsCheckoutData()->getNupointsToRedeem()
            );
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
        $this->setData('add_value', $value);
        $this->setData('is_convert', $isConvert);
        if ($this->getData('is_convert')) {
            $this->setData('add_value', $this->getConvertedMoneyToNupoints($this->getData('add_value')));
        }
        $this->setNupoints((int)$this->getNupoints() + (int)$this->getData('add_value'));
        $this->_eventManager->dispatch($this->_eventPrefix . '_add_nupoints_after', [$this->_eventObject => $this]);
        return $this;
    }

    /**
     * @return $this
     */
    public function fillNupointsToMoneyRates()
    {
        $nuPoints = $this->json->unserialize($this->scopeConfig->getValue(static::NUPOINT_SETTING_LIST));
        foreach ($nuPoints as $rate) {
            if (!empty($rate['money'])) {
                $this->_nuPointsToMoneyRates[$rate['money']]['from'] = !empty($rate['nupoints_from'])
                    ? $rate['nupoints_from'] : null;
                $this->_nuPointsToMoneyRates[$rate['money']]['to'] = !empty($rate['nupoints_to'])
                    ? $rate['nupoints_to'] : null;
                $this->_nuPointsToMoneyRates[$rate['money']]['related_product'] = !empty($rate['related_product'])
                    ? $rate['related_product'] : null;
            }
        }
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