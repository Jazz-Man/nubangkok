define([
    'underscore',
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote',
], function (_,$, wrapper, quote, shippingFields) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {

            var shippingAddress = quote.shippingAddress(),
                shippingCityIdValue = $("#shipping-new-address-form [name = 'city_id'] option:selected").text();

            if(!_.isEmpty(shippingCityIdValue)){
                shippingAddress.city = shippingCityIdValue;
            }

            return originalAction(messageContainer);
        });
    };
});