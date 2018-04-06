<?php

namespace Encomage\Nupoints\Controller\Cart;

use Magento\Framework\App\Action\Context;
use \Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

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
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Encomage\Nupoints\Quote\ReCalculate
     */
    private $reCalculateQuote;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    private $productRepositoryFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    /**
     * @var QuoteResource
     */
    private $quoteResource;

    /**
     * RedeemAjax constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param QuoteResource $quoteResource
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote,
        \Magento\Checkout\Model\Session $checkoutSession,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        QuoteResource $quoteResource
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->reCalculateQuote = $reCalculateQuote;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->productRepositoryFactory = $productRepositoryFactory;
        $this->quoteResource = $quoteResource;
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
        $var = $this->getRequest()->getParam('redeem_nupoints');
        if (!empty($var)) {
            try {
                $result = $this->customerSession->getCustomer()->getNupointItem()->enableUseNupointsOnCheckout($var);
                if ($result) {
                    $product = $this->_getProductBySku($result->getNupointsCheckoutData());
                    if ($product) {
                        $quote = $this->checkoutSession->getQuote()->addItem($this->checkoutSession->getQuote()->addProduct($product));
                        $this->quoteResource->save($quote);
                    }
                }
                $this->reCalculateQuote->reCalculate();
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Related product wasn\'t added to the cart.'));
            }
        }
    }

    /**
     * @param $nuData
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getProductBySku($nuData)
    {
        $productRepository = $this->productRepositoryFactory->create();
        $product = $productRepository->get($nuData->getProduct());
        if ($product->getId()) {
            return $product;
        }
        return false;
    }
}