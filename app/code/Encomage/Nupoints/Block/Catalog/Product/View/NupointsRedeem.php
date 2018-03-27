<?php

namespace Encomage\Nupoints\Block\Catalog\Product\View;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class NupointsRedeem
 * @package Encomage\Nupoints\Block\Catalog\Product\View
 */
class NupointsRedeem extends Template
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * NupointsRedeem constructor.
     * @param Template\Context $context
     * @param CustomerSession $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerSession $customerSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getAvailableRedeemList()
    {
        $rates = $this->getNupoints()->getNupointsToMoneyRates();
        $result = [];
        foreach ($rates as $money => $rate)
        {
            $result[] = [
                'money' => $money,

                //TODO:: ADD FREE PRODUCT OBJECT;
                'product' => 'product A',

                'nupoints' => $rate['from'],
            ];
        }
        return $result;
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints
     */
    public function getNupoints()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }
}