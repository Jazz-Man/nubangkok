<?php

namespace Encomage\Nupoints\Model\Total;

class Redeem extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    protected $quoteValidator = null;

    public function __construct(\Magento\Quote\Model\QuoteValidator $quoteValidator)
    {
        $this->quoteValidator = $quoteValidator;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);


        $exist_amount = 0; //$quote->getCustomfee();
        $customfee = 2; //enter amount which you want to set
        //$balance = $customfee - $exist_amount;//final amount

        $balance = $customfee;

        $total->setTotalAmount('nupoints_redeem_total', $balance);
        $total->setBaseTotalAmount('nupoints_redeem_total', $balance);

        $total->setRedeemTotal($balance);
        $total->setBaseRedeemTotal($balance);

        //$total->setSubtotal($total->getSubtotal() - $balance);
        //$total->setBaseSubtotal($total->getBaseSubtotal() - $balance);


        return $this;
    }

    protected function clearValues(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        return [
            'code' => 'nupoints_redeem_total',
            'title' => 'Nupoints Redeem',
            'value' => 100
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
}