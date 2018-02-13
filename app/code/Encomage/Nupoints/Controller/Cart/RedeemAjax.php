<?php

namespace Encomage\Nupoints\Controller\Cart;

use Magento\Framework\App\Action\Context;

/**
 * Class RedeemAjax
 * @package Encomage\Nupoints\Controller\Cart
 */
class RedeemAjax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * RedeemAjax constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax() || !$this->customerSession->isLoggedIn()) {

            //TODO: fix
            return;
        }

        /** @var \Encomage\Customer\Model\Customer $customer */
        $customer = $this->customerSession->getCustomer();
        $moneyFromNupoints = $customer->getNupointItem()->getConvertedNupointsToMoney();
        if ($moneyFromNupoints) {
            $quote = $this->checkoutSession->getQuote();
            $quote->setData('redeem_value', $moneyFromNupoints);
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $quote->collectTotals();
            $this->quoteRepository->save($quote);
        }
    }
}