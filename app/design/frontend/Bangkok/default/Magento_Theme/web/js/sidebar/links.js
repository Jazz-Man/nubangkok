define([
    'jquery'
], function ($) {
    "use strict";

    $('.js-left-link').each(function (index, value) {
        if ($('body').hasClass($(value).data('pageClass'))) {
            $(value).addClass('active');
        }
    })
});
