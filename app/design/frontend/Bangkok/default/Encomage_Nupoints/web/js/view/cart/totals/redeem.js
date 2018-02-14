define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/cart/cache'
], function ($, Component, quote, customerData, defaultTotal, cartCache) {
    'use strict';

    return Component.extend({

        isLogged: function () {
            var customer = customerData.get('customer');
            return customer() && customer().firstname;
        },
        getCustomerNupoints: function () {
            return this.getNupointsData().value;
        },

        getNupointsData: function () {
            return customerData.get('nupoints')();
        },

        getValue: function () {
            return this.getFormattedPrice(this.getNupointsData().redeem_value);
        },

        applyRedeem: function () {
            var $this = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: $this.ajaxUrl,
                data: {},
                success: function (response) {
                    cartCache.set('totals', null);
                    defaultTotal.estimateTotals();
                },
                error: function (error) {
                    //TODO
                }
            });
        }
    });
});
