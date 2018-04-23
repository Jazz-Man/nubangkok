<?php

namespace Encomage\Nupoints\Model\Total;

use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Customer\Model\Session as CustomerSession;

class Redeem extends AbstractTotal
{
    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * Redeem constructor.
     * @param CustomerSession $customerSession
     */
    public function __construct(CustomerSession $customerSession)
    {
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

        /** @var \Encomage\Customer\Model\Customer $customer */
        $nupointItem = $this->getNupointItem();
        if (!$nupointItem) {
            return $this;
        }
        $nupointsCheckoutData = $nupointItem->getCustomerNupointsCheckoutData();
        if (!$nupointsCheckoutData || !$this->customerSession->isLoggedIn()) {
            $total->setNupointsRedeemTotal(0);
            $total->setBaseNupointsRedeemTotal(0);
        } else {
            if (!$total->getSubtotal() && !$total->getBaseSubtotal()) {
                $total->setNupointsRedeemTotal(0);
                $total->setBaseNupointsRedeemTotal(0);
            } else {
                if ($nupointsCheckoutData instanceof \Magento\Framework\DataObject) {
                    $redeem = $nupointsCheckoutData->getMoneyToRedeem();
                    if ($redeem) {
                        $total->setNupointsRedeemTotal(-$redeem);
                        $total->setBaseNupointsRedeemTotal(-$redeem);

                        //$total->setTotalAmount('nupoints_redeem_total', -$redeem);
                        //$total->setBaseTotalAmount('nupoints_redeem_total', -$redeem);

                        if ($total->getSubtotal() < $redeem) {
                            $redeem = $total->getSubtotal();
                        }
                        $total->setSubtotal($total->getSubtotal() - $redeem);
                        $total->setBaseSubtotal($total->getBaseSubtotal() - $redeem);

                        $total->setTotalAmount('subtotal', $total->getSubtotal());
                        $total->setBaseTotalAmount('subtotal', $total->getSubtotal());
                    }
                }
            }
        }
        return $this;
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return array
     */
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
        if ($this->getNupointItem() && $this->getNupointItem()->getCustomerNupointsCheckoutData()) {
            return (int)$this->getNupointItem()->getConvertedNupointsToMoney();
        }
        return 0;
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints
     */
    public function getNupointItem()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }
}