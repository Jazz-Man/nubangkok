<?php

namespace Encomage\Nupoints\Controller\Cart;

use Magento\Framework\App\Action\Context;
use \Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class RedeemAjax
 * @package Encomage\Nupoints\Controller\Cart
 */
class RedeemAjax extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Encomage\Nupoints\Quote\ReCalculate
     */
    private $reCalculateQuote;


    /**
     * RedeemAjax constructor.
     * @param Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote
     */
    public function __construct(
        Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->reCalculateQuote = $reCalculateQuote;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            throw new NotFoundException(__('Incorrect method.'));

        }
        if (!$this->customerSession->isLoggedIn()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->_redirect->getRefererUrl());
            return $resultRedirect;
        }
        $this->checkoutSession->setUseCustomerNuPoints(true);
        $this->reCalculateQuote->reCalculate();
    }
}