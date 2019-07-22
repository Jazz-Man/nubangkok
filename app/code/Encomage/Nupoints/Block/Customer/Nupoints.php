<?php

namespace Encomage\Nupoints\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Customer\Model\Session;

/**
 * Class Nupoints
 * @package Encomage\Nupoints\Block\Customer
 */
class Nupoints extends Template
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * Nupoints constructor.
     * @param Template\Context $context
     * @param Session $customerSession
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Session $customerSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Encomage\Customer\Model\Customer|\Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return mixed
     */
    public function getNupoints()
    {
        return (int)$this->getCurrentCustomer()->getNupointItem()->getNupoints();
    }
}