<?php

namespace Encomage\Checkout\Controller\Cart;

use Magento\Checkout\Model\Cart as CustomerCart;

class RedeemAjax extends \Magento\Checkout\Controller\Cart
{
    protected $_resultJsonFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        CustomerCart $cart,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context, $scopeConfig, $checkoutSession, $storeManager, $formKeyValidator, $cart);
        $this->_resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        //TODO: implement
    }
}