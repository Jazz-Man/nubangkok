<?php

namespace Encomage\Customer\Block\Account\Dashboard;

use Magento\Framework\Exception\NoSuchEntityException;


class Address extends \Magento\Customer\Block\Account\Dashboard\Address
{
    const ADDRESS_TYPE_BILLING = 'billing',
        ADDRESS_TYPE_SHIPPING = 'shipping';

    public function getPrimaryShippingAddress()
    {
        try {
            $address = $this->currentCustomerAddress->getDefaultShippingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default shipping address.');
        }

        return $address;
    }

    public function getPrimaryBillingAddress()
    {
        try {
            $address = $this->currentCustomerAddress->getDefaultBillingAddress();
        } catch (NoSuchEntityException $e) {
            return __('You have not set a default billing address.');
        }

        return $address;
    }

    public function getAddressEditBillingFormHtml()
    {
        return $this->_getAddressEditFormHtml(
            ($this->getPrimaryBillingAddress())
                ? $this->getPrimaryBillingAddress()->getId() : null,
            self::ADDRESS_TYPE_BILLING
        );
    }

    public function getAddressEditShippingFormHtml()
    {
        return $this->_getAddressEditFormHtml(
            ($this->getPrimaryShippingAddress())
                ? $this->getPrimaryShippingAddress()->getId() : null,
            self::ADDRESS_TYPE_SHIPPING
        );
    }

    protected function _getAddressEditFormHtml($addressId, $type)
    {
        return $this->getLayout()->createBlock('Encomage\Customer\Block\Address\Edit')
            ->setAddressId($addressId)
            ->toHtml();
    }
}