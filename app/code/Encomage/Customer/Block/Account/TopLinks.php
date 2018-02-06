<?php

namespace Encomage\Customer\Block\Account;

use Magento\Framework\UrlInterface;

class TopLinks extends \Magento\Customer\Block\Account\Link
{


    protected $urlBuilder;

    protected $_session;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = [],
        UrlInterface $urlBuilder,
        \Magento\Customer\Model\Session $session
    )
    {
        parent::__construct($context, $customerUrl, $data);
        $this->urlBuilder = $urlBuilder;
        $this->_session = $session;
    }


    public function getRegisterUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/create');
    }

    public function getAccountUrl()
    {
        return $this->urlBuilder->getUrl('customer/account');
    }

    public function getLogoutUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/logout');
    }

    public function getSignInUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/login');
    }

    public function isLoggedIn()
    {
        return $this->_session->isLoggedIn();
    }

    public function getCustomerName()
    {
        return $this->_session->getCustomer()->getFirstname();
    }
}
