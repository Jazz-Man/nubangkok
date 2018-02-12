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
     * Information constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Customer\Helper\View $helperView
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
     * @return array|mixed
     */
    public function getLineId()
    {
        $attr = $this->getCustomer()->getCustomAttributes();
        if ($attr) {
            $lineId = [];
            foreach ($attr as $item) {
                $lineId = $item->getValue();
            };
            return $lineId;
        }
        return null;
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
        $genderCodeId = $this->getCustomer()->getGender();
        if ($genderCodeId) {
            switch ((int)$genderCodeId) {
                case 1:
                    return "Male";
                case 2:
                    return "Female";
                case 3:
                    return "Not Specified";
            }
        }
        return null;
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