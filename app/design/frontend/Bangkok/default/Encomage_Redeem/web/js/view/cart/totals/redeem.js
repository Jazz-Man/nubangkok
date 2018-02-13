define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote'
], function ($, Component, quote) {
    'use strict';

    return Component.extend({

        getCustomerRedeem: function () {
            return this.getFormattedPrice(1000);
        },


        getDiscountValue: function () {
            var totals = quote.getTotals()();
            if (totals) {
                return totals.discount_amount;
            }
            return quote.discount_amount;
        },
        getValue: function () {
            return this.getFormattedPrice(this.getDiscountValue());
        },

        applyRedeem: function () {
            var $this = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: $this.ajaxUrl,
                data: {},
                success: function (response) {
                    //TODO
                },
                error: function (error) {
                    //TODO
                }
            });
        }
    });
});
