<?php

namespace Encomage\Nupoints\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class Nupoints
 * @package Encomage\Nupoints\CustomerData
 */
class Nupoints implements SectionSourceInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * Nupoints constructor.
     * @param CustomerSession $customerSession
     */
    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        return [
            'value' => $this->_getCustomerNupontsCount(),
            'is_can_redeem' => $this->_getIsEnoughNuPointsForRedeem(),
            'is_used_nupoints' => (bool)$this->_getNupointItem()->getCustomerNupointsCheckoutData(),
            'redeem_value' => $this->_getRedeemValue()
        ];
    }

    protected function _getRedeemValue()
    {
        if ($this->customerSession->isLoggedIn() && $this->_getNupointItem()->getCustomerNupointsCheckoutData()) {
            return (int)$this->_getNupointItem()->getCustomerNupointsCheckoutData()->getMoneyToRedeem();
        }
        return 0;
    }

    /**
     * @return int
     */
    protected function _getCustomerNupontsCount()
    {
        if ($this->customerSession->isLoggedIn()) {
            if (!$this->_getNupointItem()->getCustomerNupointsCheckoutData()) {
                return (int)$this->_getNupointItem()->getNupoints();
            }
            return (int)$this->_getNupointItem()->getAvailableNupoints();
        }
        return 0;
    }

    /**
     * @return bool
     */
    protected function _getIsEnoughNuPointsForRedeem()
    {
        return $this->_getNupointItem()->isCanCustomerRedeem();
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints $nuPointsItem
     */
    protected function _getNupointItem()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }
}
