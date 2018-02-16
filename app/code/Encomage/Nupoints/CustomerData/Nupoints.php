<?php

namespace Encomage\Nupoints\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * Nupoints constructor.
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(CustomerSession $customerSession, CheckoutSession $checkoutSession)
    {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        return [
            'value' => $this->_getCustomerNupontsCount(),
            'is_can_redeem' => $this->_getIsEnoughNuPointsForRedeem(),
            'is_used_nupoints' => (bool)$this->checkoutSession->getUseCustomerNuPoints(),
            'redeem_value' => $this->_getRedeemValue()
        ];
    }

    protected function _getRedeemValue()
    {
        if ($this->customerSession->isLoggedIn()) {
            if ($this->checkoutSession->getUseCustomerNuPoints()) {
                return (int)$this->checkoutSession->getNupointsRedeemedMoney();
            }
        }
        return 0;
    }

    /**
     * @return int
     */
    protected function _getCustomerNupontsCount()
    {
        if ($this->customerSession->isLoggedIn()) {
            if (!$this->checkoutSession->getUseCustomerNuPoints()) {
                return (int)$this->_getNupointItem()->getNupoints();
            }
            return (int)$this->_getNupointItem()->getNupoints() - $this->_getNupointItem()->getConvertedNupointsToMoney(null, true);
        }
        return 0;
    }

    protected function _getIsEnoughNuPointsForRedeem()
    {
        return (bool)!$this->checkoutSession->getUseCustomerNuPoints()
            && $this->_getNupointItem()->getNupoints() >= $this->_getNupointItem()->getMinNuPointsCountForRedeem();
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints $nuPointsItem
     */
    protected function _getNupointItem()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }
}
