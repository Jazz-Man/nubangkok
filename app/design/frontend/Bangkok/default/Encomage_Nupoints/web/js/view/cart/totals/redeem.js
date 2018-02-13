define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/customer-data'
], function ($, Component, quote, customerData) {
    'use strict';

    return Component.extend({

        getCustomerNupoints: function () {
            var customerNupoints = customerData.get('nupoints');
            //return this.getFormattedPrice(customerNupoints().value);
            return customerNupoints().value;
        },

        getDiscountValue: function () {
            var totals = quote.getTotals()();
            debugger;
            if (totals) {
                return totals.discount_amount;
            }
            return quote.discount_amount;
        },
        getValue: function () {
            return this.getFormattedPrice(this.getDiscountValue());
        },

        applyRedeem: function () {
            debugger;
            var $this = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: $this.ajaxUrl,
                data: {},
                success: function (response) {
                    //TODO
                    debugger;
                },
                error: function (error) {
                    //TODO
                }
            });
        }
    });
});
