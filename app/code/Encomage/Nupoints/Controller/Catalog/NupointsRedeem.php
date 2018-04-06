<?php

namespace Encomage\Nupoints\Controller\Catalog;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface as CartRepository;

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
     * @var ProductRepositoryInterfaceFactory
     */
    private $productRepositoryFactory;

    /**
     * @var QuoteResource
     */
    private $cartRepository;

    /**
     * NupointsRedeem constructor.
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param CustomerSession $customerSession
     * @param JsonFactory $resultJsonFactory
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param CartRepository $cartRepository
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        CustomerSession $customerSession,
        JsonFactory $resultJsonFactory,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        CartRepository $cartRepository
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->cartRepository = $cartRepository;
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
        $nuPoints = $this->_getNupointsItem();

        if (!$nuPoints->isCanCustomerRedeem()) {
            $response['success'] = false;
        } else {
            $nuPointsToRedeem = $this->getRequest()->getParam('redeem_nupoints');
            $nuPoints->enableUseNupointsOnCheckout($nuPointsToRedeem);
            $this->_addProductToCart($nuPointsToRedeem);
            $response['success'] = true;
            $response['customer_nupoints'] = $nuPoints->getAvailableNupoints();
        }
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }

    /**
     * @param $nuPoints
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _addProductToCart($nuPoints)
    {
        $nuPointsList = $this->_getNupointsItem()->getNupointsToMoneyRates();
        $productSku = false;
        foreach ($nuPointsList as $item) {
            if ($nuPoints == $item['from']) {
                $productSku = $item['related_product'];
                break;
            }
        }
        if ($productSku) {
            $productRepository = $this->productRepositoryFactory->create();
            $product = $productRepository->get($productSku);
            if ($product->getId()) {
                $quote = $this->checkoutSession->getQuote();
                $quote->addItem($quote->addProduct($product));
                $this->cartRepository->save($quote);
            }
        }
        return $this;
    }

    /**
     * @return mixed
     */
    protected function _getNupointsItem()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }
}