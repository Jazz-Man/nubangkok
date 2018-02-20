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
            var customer = customerData.get('customer'), self = this;
            $('.customer-links').hide();
            if (customer()['website_id'] !== window.checkout.websiteId) {
                customerData.reload(['customer'], false);
            }
            mediaCheck({
                media: '(max-width: 768px)',
                entry: $.proxy(function () {
                    self.targetMobile()
                }, this),
                exit: $.proxy(function () {
                    self.targetDesktop()
                }, this)
            });

            return this._super();
        },
        getLinks: function () {
            var customer = customerData.get('customer')(),
                links = customerData.get('customer')().customerTopLinks;
            if (links) {
                $('.customer-links').show();

                for (var i = 0; i < links.length; i++) {
                    links[i].cssClass = ($('body').hasClass(links[i].handle)) ? 'active' : '';
                }
            } else {
                $('.customer-links').hide();
            }
            if (customer.firstname) {
                $('a.customer-name')
                    .removeClass('top-link-myaccount')
                    .text($.mage.__('Hi') + ' ' + customer.firstname);
            }
            return links;
        },
        targetDesktop: function () {
            //todo: desktop
        },
        targetMobile: function () {

            $(document).on('click', 'a.js-customer-top-link', function () {
                $('html').addClass('mobile-account-top-links');
                event.preventDefault();
            });
            $(document).on('click', '.close-account-links-js', function () {
                $('html').removeClass('mobile-account-top-links');
                event.preventDefault();
            })
        }
    });

});