define([
    'underscore',
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function (_,$, wrapper, quote) {
    'use strict';

    return function (setBillingAddressAction) {
        return wrapper.wrap(setBillingAddressAction, function (originalAction, messageContainer) {
            var billingAddress = quote.billingAddress(),
                shippingAddress = quote.shippingAddress(),
                shippingCityIdValue = $("#shipping-new-address-form [name = 'city_id'] option:selected").text();

            if(!_.isEmpty(shippingCityIdValue)){
                shippingAddress.city = shippingCityIdValue;
            }

            return originalAction(messageContainer);
        });
    };
});