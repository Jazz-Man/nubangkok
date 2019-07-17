<?php

namespace Encomage\Nupoints\Controller\Cart;

use Encomage\Nupoints\Quote\ReCalculate;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use \Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface as CartRepository;

/**
 * Class RedeemAjax
 * @package Encomage\Nupoints\Controller\Cart
 */
class RedeemAjax extends Action
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
    private $cartRepository;

    /**
     * RedeemAjax constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param CartRepository $cartRepository
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        ReCalculate $reCalculateQuote,
        Session $checkoutSession,
        ProductRepositoryInterfaceFactory $productRepositoryFactory,
        CartRepository $cartRepository
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->reCalculateQuote = $reCalculateQuote;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
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
        $var = $this->getRequest()->getParam('redeem_nupoints');
        if (!empty($var)) {
            try {
                $result = $this->customerSession->getCustomer()->getNupointItem()->enableUseNupointsOnCheckout($var);
                if ($result && $product = $this->_getProductBySku($result->getNupointsCheckoutData())) {
                    $quote = $this->checkoutSession->getQuote();
                    $quote->addProduct($product);
                    $this->cartRepository->save($quote);
                }
                $this->reCalculateQuote->reCalculate();
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage(__('Related product wasn\'t added to the cart.'));
            }
        }
    }

    /**
     * @param $nuData
     *
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     */
    protected function _getProductBySku($nuData)
    {
        if ($nuData->getProduct()) {
            $productRepository = $this->productRepositoryFactory->create();
            $product = $productRepository->get($nuData->getProduct());
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }
}