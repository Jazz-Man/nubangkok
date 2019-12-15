<?php
namespace Encomage\ErpIntegration\Observer;

use Encomage\ErpIntegration\Helper\ErpApiCustomer;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CustomerRegisterSuccess
 * @package Encomage\ErpIntegration\Observer
 */
class CustomerRegisterSuccess implements ObserverInterface
{

    /**
     * @var \Encomage\ErpIntegration\Helper\ErpApiCustomer
     */
    private $erpApiCustomer;

    /**
     * CustomerRegisterSuccess constructor.
     *
     * @param \Encomage\ErpIntegration\Helper\ErpApiCustomer $erpApiCustomer
     */
    public function __construct(
        ErpApiCustomer $erpApiCustomer
    )
    {
        $this->erpApiCustomer = $erpApiCustomer;
    }

    /**
     * @param Observer $observer
     *
     * @throws \Exception
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getCustomer()->getId();


        $this->erpApiCustomer->getCustomerErpCode($customerId);
    }
}