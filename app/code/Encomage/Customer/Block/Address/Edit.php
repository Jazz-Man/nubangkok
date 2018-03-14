<?php
/**
 * @method setAddressId(int $id);
 * @method int getAddressId();
 * @method setAddressType(string $type);
 * @method string getAddressType();
 */

namespace Encomage\Customer\Block\Address;

use Magento\Framework\Exception\NoSuchEntityException;
use Encomage\Customer\Block\Account\Dashboard\Address;

class Edit extends \Magento\Customer\Block\Address\Edit
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Customer::address/edit.phtml');
    }

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        // Init address object
        if ($addressId = $this->getAddressId()) {
            try {
                $this->_address = $this->_addressRepository->getById($addressId);
                if ($this->_address->getCustomerId() != $this->_customerSession->getCustomerId()) {
                    $this->_address = null;
                }
            } catch (NoSuchEntityException $e) {
                $this->_address = null;
            }
        }

        if ($this->_address === null || !$this->_address->getId()) {
            $this->_address = $this->addressDataFactory->create();
            $customer = $this->getCustomer();
            $this->_address->setPrefix($customer->getPrefix());
            $this->_address->setFirstname($customer->getFirstname());
            $this->_address->setMiddlename($customer->getMiddlename());
            $this->_address->setLastname($customer->getLastname());
            $this->_address->setSuffix($customer->getSuffix());
        }

        $this->pageConfig->getTitle()->set($this->getTitle());

        if ($postedData = $this->_customerSession->getAddressFormData(true)) {
            $postedData['region'] = [
                'region' => $postedData['region'] ?? null,
            ];
            if (!empty($postedData['region_id'])) {
                $postedData['region']['region_id'] = $postedData['region_id'];
            }
            $this->dataObjectHelper->populateWithArray(
                $this->_address,
                $postedData,
                \Magento\Customer\Api\Data\AddressInterface::class
            );
        }

        return $this;
    }

    protected function _prepareLayout()
    {
        return $this;
    }

    public function getSuccessUrl()
    {
        return $this->getUrl('customer/account');
    }

    public function getErrorUrl()
    {
        return $this->getUrl('customer/account');
    }

    public function isShippingTypeRender()
    {
        return $this->getAddressType() === Address::ADDRESS_TYPE_SHIPPING;
    }

    public function getCountryHtmlSelect($defValue = null, $name = 'country_id', $id = 'country', $title = 'Country')
    {
        $id .= '-' . $this->getAddressType();
        return parent::getCountryHtmlSelect($defValue, $name, $id, $title);
    }
}