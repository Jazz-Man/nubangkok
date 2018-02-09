define([
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function (Component, quote) {
    'use strict';

    return Component.extend({

        getCustomerRedeem: function () {
            return this.getFormattedPrice(1000);
        },

        getValue: function () {
            return 777;
        }
    });
});
