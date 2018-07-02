<?php
namespace Encomage\ErpIntegration\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Encomage\ErpIntegration\Model\Api\Customer as ApiCustomer;

/**
 * Class CustomerRegisterSuccess
 * @package Encomage\ErpIntegration\Observer
 */
class CustomerRegisterSuccess implements ObserverInterface
{
    /**
     * @var ApiCustomer
     */
    private $apiCustomer;

    /**
     * CustomerRegisterSuccess constructor.
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
        $customerId = $observer->getCustomer()->getId();
        $phone = null;
        if ($observer->getAccountController()) {
            $params = $observer->getAccountController()->getRequest()->getParams();
            $phone = $params['telephone'];
        }
        $this->apiCustomer->createOrUpdateCustomer($customerId, $phone);
    }
}