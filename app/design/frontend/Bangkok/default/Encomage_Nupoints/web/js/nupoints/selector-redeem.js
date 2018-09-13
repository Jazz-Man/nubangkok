define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';
    return function (config, element) {
        function initUi(customerDataObject) {
            var customerNupointsValue = (customerDataObject.value) ? customerDataObject.value : 0;
            $element.find('.js-customer-nupoints-balance').html(customerNupointsValue);
            if (!customerDataObject.is_can_redeem) {
                $element.find('.js-customer-nuponts-redeem').attr('disabled', true);
            }
        }
        var $element = $(element),
            customerNupoints = customerData.get('nupoints')(),
            expiredSectionNames = customerData.getExpiredSectionNames();

        customerData.get('nupoints').subscribe(function (updatedCart) {
            initUi(updatedCart);
        }, this);

        if (expiredSectionNames.length > 0){
            expiredSectionNames.forEach(function (index, value) {
                if (index == 'customer-data') customerData.reload(['nupoints'], false);
            });
        }

        if (!customerNupoints.data_id) {
            customerData.reload(['nupoints'], false);
        } else {
            initUi(customerNupoints);

        }
    }
});
