<?php

namespace Encomage\Nupoints\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Encomage\Nupoints\Api\NupointsRepositoryInterface;

/**
 * Class PlaceOrderAfter
 * @package Encomage\Nupoints\Observer\Sales
 */
class PlaceOrderAfter implements ObserverInterface
{
    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var NupointsRepositoryInterface
     */
    private $nupointsRepository;

    /**
     * PlaceOrderAfter constructor.
     * @param Session $customerSession
     * @param NupointsRepositoryInterface $nupointsRepository
     */
    public function __construct(Session $customerSession, NupointsRepositoryInterface $nupointsRepository)
    {
        $this->customerSession = $customerSession;
        $this->nupointsRepository = $nupointsRepository;
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        /** @var \Encomage\Customer\Model\Customer $customer */
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $customerNupointItem = $customer->getNupointItem();
            $customerNupointItem->addNupoints($observer->getOrder()->getSubtotal(), true);
            $this->nupointsRepository->save($customerNupointItem);
        }
    }
}