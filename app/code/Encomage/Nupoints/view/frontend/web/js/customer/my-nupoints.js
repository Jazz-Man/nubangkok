define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';
    return function (config, element) {
        var myNupoints = customerData.get('nupoints')();
        if (myNupoints.value) {
            element.innerText = myNupoints.value;
        } else {
            element.innerText = 0;
        }
    }
});
