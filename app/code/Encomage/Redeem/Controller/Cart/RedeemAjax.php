<?php

namespace Encomage\Redeem\Controller\Cart;

use Magento\Framework\App\Action\Context;

class RedeemAjax extends \Magento\Framework\App\Action\Action
{
    protected $resultJsonFactory;
    protected $quoteRepository;
    protected $checkoutSession;

    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->quoteRepository = $quoteRepository;
    }

    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {

            //TODO: fix
            return;
        }

        $quote = $this->checkoutSession->getQuote();
        //TODO: get customer redeem;
        $quote->setData('redeem_value', 2);
        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->collectTotals();
        $this->quoteRepository->save($quote);


    }
}