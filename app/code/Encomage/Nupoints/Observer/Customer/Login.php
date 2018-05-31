<?php

namespace Encomage\Nupoints\Observer\Customer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Encomage\Nupoints\Model\NupointsRepository;
use Encomage\ErpIntegration\Model\Api\Points;
/**
 * Class PlaceOrderBefore
 * @package Encomage\Nupoints\Observer\Sales
 */
class Login implements ObserverInterface
{
    /**
     * @var NupointsRepository
     */
    private $nupointsRepository;
    /**
     * @var CustomerSession
     */
    private $customerSession;
    /**
     * @var Points
     */
    private $apiPoints;
    
    public function __construct(CustomerSession $customerSession, NupointsRepository $nupointsRepository, Points $apiPoints)
    {
        $this->apiPoints = $apiPoints;
        $this->customerSession = $customerSession;
        $this->nupointsRepository = $nupointsRepository;
    }

    public function execute(Observer $observer) {
        $customer = $this->customerSession->getCustomer();
        if ($customer->getErpCustomerCode()) {
            $this->apiPoints->getNuPointByCustomerCode($customer->getErpCustomerCode());
        }
    }
}