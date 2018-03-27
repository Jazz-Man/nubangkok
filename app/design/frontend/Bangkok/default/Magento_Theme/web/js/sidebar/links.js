define([
    'jquery'
], function ($) {
    "use strict";

    return function (config, element) {
        $.each($(element).find('.js-left-link'), function (index, value) {
            if ($('body').hasClass($(value).data('pageClass'))) {
                $(value).addClass('active');
            }
        })
    };
});
