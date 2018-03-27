<?php

namespace Encomage\Nupoints\Controller\Catalog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Class NupointsRedeem
 * @package Encomage\Nupoints\Controller\Catalog
 */
class NupointsRedeem extends \Magento\Framework\App\Action\Action
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
     * NupointsRedeem constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
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
        $response = [];
        /** @var \Encomage\Nupoints\Model\Nupoints $nuPoints */
        $nuPoints = $this->customerSession->getCustomer()->getNupointItem();

        if (!$nuPoints->isCanCustomerRedeem()) {
            $response['success'] = false;
        } else {
            $this->checkoutSession->setUseCustomerNuPoints(true);
            $response['success'] = false;
            $response['customer_nupoints'] = $nuPoints->getNupoints();
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}