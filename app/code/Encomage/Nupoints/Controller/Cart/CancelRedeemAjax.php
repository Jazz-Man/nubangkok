<?php

namespace Encomage\Nupoints\Controller\Cart;

use Magento\Framework\App\Action\Context;
use \Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Checkout\Model\Session;
use Magento\Quote\Model\ResourceModel\Quote;

/**
 * Class RevertRedeemAjax
 * @package Encomage\Nupoints\Controller\Cart
 */
class CancelRedeemAjax extends \Magento\Framework\App\Action\Action
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
     * @var Session
     */
    private $checkoutSession;
    /**
     * @var Quote
     */
    private $quoteResource;

    /**
     * CancelRedeemAjax constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote
     * @param Session $checkoutSession
     * @param Quote $quoteResource
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Encomage\Nupoints\Quote\ReCalculate $reCalculateQuote,
        Session $checkoutSession,
        Quote $quoteResource
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->reCalculateQuote = $reCalculateQuote;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
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
        $this->_removeRelatedProduct();
        $this->_getNupointItem()->disableUseNupointsOnCheckout();
        $this->reCalculateQuote->reCalculate();
    }

    /**
     * Remove nuPoint related product
     *
     * @return $this
     */
    protected function _removeRelatedProduct()
    {
        $itemToDelete = $this->_getItemToDelete();
        if ($itemToDelete) {
            try {
                $this->quoteResource->save($this->checkoutSession->getQuote()->deleteItem($itemToDelete));
                $this->messageManager->addSuccessMessage(__('NuPoints and gift product are removed from the cart.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Gift product is not removed from the cart.'));
            }
        }
        return $this;
    }

    /**
     * Get product from quote by sku.
     *
     * @return null
     */
    protected function _getItemToDelete()
    {
        $nuPoint = $this->_getNupointItem()->getCustomerNupointsCheckoutData();
        $checkoutItems = $this->checkoutSession->getQuote()->getAllItems();
        $searchItem = null;
        foreach ($checkoutItems as $item) {
            if ($item->getSku() == $nuPoint->getProduct()) {
                $searchItem = $item;
                break;
            }
        }
        return $searchItem;
    }

    /**
     * @return \Encomage\Nupoints\Model\Nupoints
     * @return mixed
     */
    protected function _getNupointItem()
    {
        return $this->customerSession->getCustomer()->getNupointItem();
    }
}