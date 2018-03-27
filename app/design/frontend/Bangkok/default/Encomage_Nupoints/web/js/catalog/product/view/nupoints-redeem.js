define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function ($, customerData) {
    'use strict';
    return function (config, element) {
        var $element = $(element),
            customerNupoints = customerData.get('nupoints')(),
            customerNupointsValue = (customerNupoints.value) ? customerNupoints.value : 0;
        $element.find('.js-customer-nupoints-balance').html(customerNupointsValue);
    }
});
