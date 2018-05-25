<?php
namespace Encomage\ErpIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Encomage\ErpIntegration\Model\Api\Customer as ApiCustomer;

/**
 * Class CustomerAddressSaveSuccess
 * @package Encomage\ErpIntegration\Observer
 */
class CustomerAddressSaveSuccess implements ObserverInterface
{
    /**
     * @var ApiCustomer
     */
    private $apiCustomer;

    /**
     * CustomerAddressSaveSuccess constructor.
     * @param ApiCustomer $apiCustomer
     */
    public function __construct(ApiCustomer $apiCustomer)
    {
        $this->apiCustomer = $apiCustomer;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $customerId = $observer->getAddress()->getCustomerId();
        if ($customerId) {
            $this->apiCustomer->createOrUpdateCustomer($customerId, null);
        }
        
    }
}