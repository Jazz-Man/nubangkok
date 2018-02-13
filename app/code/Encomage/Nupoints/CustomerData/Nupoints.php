<?php

namespace Encomage\Nupoints\CustomerData;

use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Customer\Model\Session;

/**
 * Class Nupoints
 * @package Encomage\Nupoints\CustomerData
 */
class Nupoints implements SectionSourceInterface
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * Nupoints constructor.
     * @param Session $customerSession
     */
    public function __construct(Session $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @return array
     */
    public function getSectionData()
    {
        return ['value' => $this->_getCustomerNupontsCount()];
    }

    /**
     * @return int
     */
    protected function _getCustomerNupontsCount()
    {
        if ($this->customerSession->isLoggedIn()) {
            return (int)$this->customerSession->getCustomer()->getNupointItem()->getNupoints();
        }
        return 0;
    }
}
