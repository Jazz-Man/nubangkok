<?php

namespace Encomage\Nupoints\Observer\Predispatch\Checkout;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Catalog\Api\ProductRepositoryInterfaceFactory;

/**
 * Class CartAdd
 * @package Encomage\Nupoints\Observer\Predispatch\Checkout
 */
class CartAdd implements ObserverInterface
{
    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var ProductRepositoryInterfaceFactory
     */
    private $productRepositoryFactory;

    /**
     * CartAdd constructor.
     * @param CustomerSession $session
     * @param ProductRepositoryInterfaceFactory $productRepositoryInterfaceFactory
     */
    public function __construct(CustomerSession $session, ProductRepositoryInterfaceFactory $productRepositoryInterfaceFactory)
    {
        $this->customerSession = $session;
        $this->productRepositoryFactory = $productRepositoryInterfaceFactory;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $productId = $this->_getRelatedProductId();
        if ($productId) {
            $relatedProduct = $observer->getRequest()->getParam('related_product', null);
            if ($relatedProduct) {
                $relatedProduct .= ',' . $productId;
            } else {
                $relatedProduct = $productId;
            }
            $observer->getRequest()->setParam('related_product', $relatedProduct);
        }
    }

    /**
     * @return bool|int|null
     */
    protected function _getRelatedProductId()
    {
        if (!$this->_getNupointsCheckoutData() || !$this->_getNupointsCheckoutData()->getProduct()) {
            return false;
        }
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
        $productRepository = $this->productRepositoryFactory->create();
        $product = $productRepository->get($this->_getNupointsCheckoutData()->getProduct());
        if (!$product->getId()) {
            return false;
        }
        return $product->getId();
    }

    /**
     * @return mixed
     */
    protected function _getNupointsCheckoutData()
    {
        return $this->customerSession->getCustomer()->getNupointItem()->getCustomerNupointsCheckoutData();
    }
}