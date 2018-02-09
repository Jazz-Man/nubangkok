define([
    'jquery',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/quote',
    'ko'
], function ($, Component, quote, ko) {
    'use strict';

    return Component.extend({
        couponCode: ko.observable(""),
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

        applyPromoCode: function () {
            debugger;
            var $this = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: this.ajaxUrl,
                data: {coupon_code: $this.couponCode._latestValue},
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
