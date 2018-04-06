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

        isCanCancelRedeem: function () {
            return this.getNupointsData().is_used_nupoints;
        },

        isCanRedeem: function () {
            return this.getNupointsData().is_can_redeem
        },

        getNupointsData: function () {
            return customerData.get('nupoints')();
        },

        getValue: function () {
            return this.getFormattedPrice(this.getNupointsData().redeem_value);
        },

        applyRedeem: function () {
            var $this = this,
                amount = $('.redeem-nupoints').val();
            if (amount) {
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    data: {"redeem_nupoints":amount},
                    url: $this.ajaxUrl,
                    success: function (response) {
                        cartCache.set('totals', null);
                        defaultTotal.estimateTotals();
                        $this.updateItemsList();
                    },
                    error: function (error) {
                        //TODO
                        
                    }
                });
            }

        },
        revertRedeem: function () {
            var $this = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: $this.revertAjaxUrl,
                success: function (response) {
                    cartCache.set('totals', null);
                    defaultTotal.estimateTotals();
                    $this.updateItemsList();
                },
                error: function (error) {
                    //TODO
                }
            });
        },
        updateItemsList: function () {
            var $this = this;
            $.ajax({
                type: "POST",
                dataType: "json",
                url: $this.cartPageUrl,
                data:{'ajax_get_block':'checkout.cart.form.content'},
                success: function (response) {
                    $('form.form.form-cart').html(response.html);
                    $('.redeem-nupoints').val('');
                },
                error: function (error) {
                    //TODO
                }
            });
        }
    });
});
