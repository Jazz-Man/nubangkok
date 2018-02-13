<?php

namespace Encomage\Customer\Block\Account\Dashboard;

class Info extends \Magento\Customer\Block\Account\Dashboard\Info
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
     * Information constructor.
     * @param \Magento\Customer\Helper\Session\CurrentCustomerAddress $currentCustomerAddress
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
     * @param \Magento\Directory\Model\CountryFactory $countryFactory
     * @param array $data
     */
    public function __construct(
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
    public function getTelephone()
    {
        return $this->getBillingAddress() ? $this->getBillingAddress()->getTelephone() : null;
    }

    /**
     * @return \Magento\Customer\Api\Data\AddressInterface|null
     */
    public function getBillingAddress()
    {
        return $this->currentCustomerAddress->getDefaultBillingAddress();
    }


}