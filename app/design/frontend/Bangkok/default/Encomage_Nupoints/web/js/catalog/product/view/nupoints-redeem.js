define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url'
], function ($, customerData, urlBuilder) {
    'use strict';
    return function (config, element) {
        var $element = $(element),
            customerNupoints = customerData.get('nupoints')(),
            customerNupointsValue = (customerNupoints.value) ? customerNupoints.value : 0,
            select = $element.find('.js-nupoints-rates'),
            redeemButton = $element.find('.js-customer-nuponts-redeem');
        $element.find('.js-customer-nupoints-balance').html(customerNupointsValue);
        redeemButton.on('click', function () {
            var redeemNupoints = select.find('.js-selected-option-container').data('selectValue');
            $.ajax({
                url: urlBuilder.build(config.nupointsRedeemUrl),
                data: {'redeem_nupoints': redeemNupoints},
                type: 'post',
                dataType: 'json',
                async: false
            });
        });
    }
});
