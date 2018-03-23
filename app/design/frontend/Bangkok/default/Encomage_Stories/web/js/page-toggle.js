define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, customerData) {
    "use strict";

    return function (config, element) {
        var ourButton = $('.js-our-story-button'),
            yorButton = $('.js-your-stories-button'),
            addButton = $('.js-add-story-button'),
            ourContent = $('.js-our-story-block'),
            yourContent = $('.js-your-stories-block');


        // enable our stories page
        ourButton.on('click', function () {
            if (!ourButton.hasClass('active')) {
                yorButton.removeClass('active');
                yourContent.hide();
                ourButton.addClass('active');
                ourContent.show();
                addButton.hide();
            }
        });

        // enable your stories page
        yorButton.on('click', function () {
            if (!yorButton.hasClass('active')) {
                ourButton.removeClass('active');
                ourContent.hide();
                yorButton.addClass('active');
                yourContent.show();
                if (customerData.get('customer')().websiteId) {
                    addButton.show();
                }
            }
        });
    }
});