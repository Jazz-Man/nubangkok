define([
    'jquery',
    'accordion'
], function ($) {
    "use strict";
    return function (config, element) {
        element = $(element);
        element.accordion();
        debugger;
        element.find('.js-edit').on('click', function () {
            var a = $(this);
            debugger;
            if (a.hasClass('edit')) {
                a.removeClass('edit').addClass('save').find('span').text($.mage.__('Save Address'));
            } else if (a.hasClass('save')) {
                element.find('form').submit();
            }
        });
    };
});