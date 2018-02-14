<?php

namespace Encomage\Nupoints\Model\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;

class Redeem extends AbstractTotal
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Redeem constructor.
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     */
    public function __construct(CheckoutSession $checkoutSession, CustomerSession $customerSession)
    {
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    )
    {
        parent::collect($quote, $shippingAssignment, $total);

        if (!$this->checkoutSession->getUseCustomerNuPoints()) {
            $total->setNupointsRedeemTotal(0);
            $total->setBaseNupointsRedeemTotal(0);
        } else {
            if (!$total->getSubtotal() && !$total->getBaseSubtotal()) {
                $total->setNupointsRedeemTotal(0);
                $total->setBaseNupointsRedeemTotal(0);
            } else {
                /** @var \Encomage\Customer\Model\Customer $customer */
                $customer = $this->customerSession->getCustomer();
                $redeem = (int)$customer->getNupointItem()->getConvertedNupointsToMoney();
                if ($redeem) {
                    $total->setNupointsRedeemTotal(-$redeem);
                    $total->setBaseNupointsRedeemTotal(-$redeem);

                    //$total->setTotalAmount('nupoints_redeem_total', -$redeem);
                    //$total->setBaseTotalAmount('nupoints_redeem_total', -$redeem);

                    $total->setSubtotal($total->getSubtotal() - $redeem);
                    $total->setBaseSubtotal($total->getBaseSubtotal() - $redeem);

                    $total->setTotalAmount('subtotal', $total->getSubtotal());
                    $total->setBaseTotalAmount('subtotal', $total->getSubtotal());
                }
            }
        }

        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => 'nupoints_redeem_total',
            'title' => 'Nupoints Redeem',
            'value' => $this->_getValue()
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __('Nupoints Redeem');
    }

    /**
     * @return int
     */
    protected function _getValue()
    {
        if ($this->checkoutSession->getUseCustomerNuPoints()) {
            return (int)$this->customerSession->getCustomer()->getNupointItem()->getConvertedNupointsToMoney();
        }
        return 0;
    }
}