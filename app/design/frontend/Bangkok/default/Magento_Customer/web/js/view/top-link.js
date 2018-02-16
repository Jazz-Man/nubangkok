define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery'
], function (Component, customerData, $) {
    'use strict';


    return Component.extend({
        /**
         * @override
         */
        initialize: function () {
            var customer = customerData.get('customer');
            $('.customer-links').hide();
            if (customer()['website_id'] !== window.checkout.websiteId) {
                customerData.reload(['customer'], false);
            }
            return this._super();
        },
        getLinks: function () {
            var customer = customerData.get('customer')(),
                links = customerData.get('customer')().customerTopLinks;
            if (links) {
                $('.customer-links').show();
            } else {
                $('.customer-links').hide();
            }
            if (customer.firstname) {
                $('a.customer-name')
                    .removeClass('top-link-myaccount')
                    .text($.mage.__('Hi') + ' ' + customer.firstname);
            }
            return customer.customerTopLinks;
        }

    });
});
