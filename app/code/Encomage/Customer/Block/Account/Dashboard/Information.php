<?php

namespace Encomage\Customer\Block\Account\Dashboard;

class Information extends \Magento\Customer\Block\Account\Dashboard\Info
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomerAddress
     */
    private $currentCustomerAddress;

    /**
     * @var \Magento\Directory\Model\CountryFactory
     */
    private $_countryFactory;

    /**
     * @var \Encomage\Customer\Model\CustomerInfo
     */
    private $_customerInfo;

    /**
     * Information constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param array $data
     */
    public function __construct(
        \Encomage\Customer\Model\CustomerInfo $customerInfo,
        \Magento\Customer\Helper\Session\CurrentCustomerAddress $currentCustomerAddress,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Customer\Helper\View $helperView,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        array $data = [])
    {
        parent::__construct($context,
            $currentCustomer,
            $subscriberFactory,
            $helperView,
            $data);
        $this->currentCustomerAddress = $currentCustomerAddress;
        $this->_countryFactory = $countryFactory;
        $this->_customerInfo =$customerInfo;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->getCustomer()->getLastname();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->getCustomer()->getFirstname();
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->getCustomer()->getEmail();
    }

    /**
     * @return null|string
     */
    public function getDob()
    {
        return $this->getCustomer()->getDob();
    }

    /**
     * @return mixed|null
     */
    public function getLineId()
    {
       return $this->_customerInfo->getLineId();
    }

    /**
     * @return null|string
     */
    public function getCountryName()
    {
        $countryId = $this->getBillingAddress() ? $this->getBillingAddress()->getCountryId() : null;
        if ($countryId) {
            $country = $this->_countryFactory->create()->loadByCode($countryId);
            return $country->getName();
        }
        return $countryId;
    }

    /**
     * @return null|string
     */
    public function getGender()
    {
      return $this->_customerInfo->getGender();
    }

    /**
     * @return null|string
     */
    public function getTelephone()
    {
        $telephone = $this->getBillingAddress() ? $this->getBillingAddress()->getTelephone() : null;
        return $telephone;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getBillingAddress()
    {
        return $this->currentCustomerAddress->getDefaultBillingAddress();
    }


}