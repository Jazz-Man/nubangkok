<?php

namespace Encomage\Nupoints\Observer\Sales;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
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
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * PlaceOrderAfter constructor.
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param NupointsRepositoryInterface $nupointsRepository
     */
    public function __construct(
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        NupointsRepositoryInterface $nupointsRepository
    )
    {
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
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
            if ($this->checkoutSession->getUseCustomerNuPoints()) {
                $customerNupointItem->redeemNupointsAfterOrderPlaced();
                $this->checkoutSession->setUseCustomerNuPoints(false);
            }
            $customerNupointItem->addNupoints($observer->getOrder()->getSubtotal(), true);
            $this->nupointsRepository->save($customerNupointItem);
        }
    }
}