<?php

namespace Encomage\Nupoints\Quote;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface as CartRepository;

/**
 * Class ReCalculate
 * @package Encomage\Nupoints\Quote
 */
class ReCalculate
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepository
     */
    private $cartRepository;

    /**
     * ReCalculate constructor.
     * @param CheckoutSession $checkoutSession
     * @param CartRepository $cartRepository
     */
    public function __construct(CheckoutSession $checkoutSession, CartRepository $cartRepository)
    {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function reCalculate()
    {
        $quote = $this->checkoutSession->getQuote();
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();
        $this->cartRepository->save($quote);
        return $this;
    }
}