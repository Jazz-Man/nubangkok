<?php

namespace Encomage\Nupoints\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;

/**
 * Class PlaceOrderBefore
 * @package Encomage\Nupoints\Observer\Sales
 */
class PlaceOrderBefore implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * PlaceOrderAfter constructor.
     * @param CustomerSession $customerSession
     */
    public function __construct(CustomerSession $customerSession)
    {
        $this->customerSession = $customerSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Encomage\Customer\Model\Customer $customer */
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $nupointsNumbers = $customer->getNupointItem()
                ->getConvertedMoneyToNupoints($observer->getOrder()->getSubtotal());
            $observer->getOrder()->setData('nupoints', $nupointsNumbers);
        }
    }
}