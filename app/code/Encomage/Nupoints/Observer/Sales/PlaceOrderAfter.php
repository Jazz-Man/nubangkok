<?php

namespace Encomage\Nupoints\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Encomage\Nupoints\Api\NupointsRepositoryInterface;

/**
 * Class PlaceOrderAfter
 * @package Encomage\Nupoints\Observer\Sales
 */
class PlaceOrderAfter implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var NupointsRepositoryInterface
     */
    private $nupointsRepository;


    /**
     * PlaceOrderAfter constructor.
     * @param CustomerSession $customerSession
     * @param NupointsRepositoryInterface $nupointsRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        NupointsRepositoryInterface $nupointsRepository
    )
    {
        $this->customerSession = $customerSession;
        $this->nupointsRepository = $nupointsRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {

        /** @var \Encomage\Customer\Model\Customer $customer */
        $customer = $this->customerSession->getCustomer();
        if ($customer->getId()) {
            $customerNupointItem = $customer->getNupointItem();
            if ($customerNupointItem->getCustomerNupointsCheckoutData()) {
                $customerNupointItem->redeemNupointsAfterOrderPlaced();
            }
            if ($observer->getOrder()->getNupoints()) {
                $customerNupointItem->addNupoints($observer->getOrder()->getNupoints());
                $this->nupointsRepository->save($customerNupointItem);
            }
        }
    }
}