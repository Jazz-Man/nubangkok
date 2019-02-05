define(['jquery',
        'ko',
        'underscore',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Customer/js/customer-data',
    ],
    function ($,
              ko,
              _,
              Component,
              customer,
              addressList,
              quote,
              createBillingAddress,
              selectBillingAddress,
              checkoutData
    ) {
        'use strict';
        var lastSelectedBillingAddress = null,
            newAddressOption = {
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });
        return function (Component) {
            return Component.extend({
                initObservable: function () {
                    this._super()
                        .observe({
                            selectedAddress: null,
                            isAddressDetailsVisible: quote.billingAddress() != null,
                            isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length === 1,
                            isAddressSameAsShipping: false,
                            saveInAddressBook: 1
                        });

                    quote.billingAddress.subscribe(function (newAddress) {

                        var shippingCityIdValue = $("#shipping-new-address-form [name = 'city_id'] option:selected").text();

                        if(!_.isEmpty(shippingCityIdValue)){
                            newAddress.city = shippingCityIdValue;
                        }
                        if (quote.isVirtual()) {
                            this.isAddressSameAsShipping(false);
                        } else {
                            this.isAddressSameAsShipping(
                                newAddress != null &&
                                newAddress.getCacheKey() == quote.shippingAddress().getCacheKey()
                            );
                        }

                        if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                            this.saveInAddressBook(newAddress.saveInAddressBook);
                        } else {
                            this.saveInAddressBook(1);
                        }
                        this.isAddressDetailsVisible(true);
                    }, this);

                    return this;
                },
                useShippingAddress: function () {
                    if (this.isAddressSameAsShipping()) {
                        selectBillingAddress(quote.shippingAddress());
                        this.updateAddresses();
                        this.isAddressDetailsVisible(true);
                    } else {
                        lastSelectedBillingAddress = quote.billingAddress();
                        quote.billingAddress(null);
                        this.isAddressDetailsVisible(false);
                    }

                    checkoutData.setSelectedBillingAddress(null);

                    return true;
                },
                updateAddress: function () {
                    var addressData, newBillingAddress, billingCityId, billingCityIdValue;

                    if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                        selectBillingAddress(this.selectedAddress());
                        checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                    } else {
                        this.source.set('params.invalid', false);
                        this.source.trigger(this.dataScopePrefix + '.data.validate');

                        if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                            this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                        }

                        if (!this.source.get('params.invalid')) {
                            addressData = this.source.get(this.dataScopePrefix);

                            if (customer.isLoggedIn() && !this.customerHasAddresses) {
                                this.saveInAddressBook(1);
                            }
                            addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                            newBillingAddress = createBillingAddress(addressData);
                            selectBillingAddress(newBillingAddress);
                            checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                            checkoutData.setNewCustomerBillingAddress(addressData);
                            billingCityId = $("#billing-new-address-form [name = 'city_id'] option:selected"),
                                billingCityIdValue = billingCityId.text();
                        }
                    }
                    newBillingAddress.city = billingCityIdValue;

                    this.updateAddresses();
                },
            });
        }
    });
