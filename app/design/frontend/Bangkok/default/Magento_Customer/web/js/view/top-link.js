define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'matchMedia'
], function (Component, customerData, $, mediaCheck) {
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
            mediaCheck({
                media: '(max-width: 768px)',
                entry: $.proxy(function () {

                    $(document).on('click', 'a.top-link-myaccount', function () {

                        // $('html').toggleClass('mobile-account-top-links');
                        $('html').addClass('mobile-account-top-links');

                        event.preventDefault();
                    });
                    $(document).on('click', '.close-account-links-js', function () {

                        // $('html').toggleClass('mobile-account-top-links');
                        $('html').removeClass('mobile-account-top-links');

                        event.preventDefault();
                    })

                }, this),
                exit: $.proxy(function () {
                    alert('desktop')

                }, this)
            });

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