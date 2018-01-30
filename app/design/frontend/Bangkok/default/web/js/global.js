define([
    'jquery',
    'domReady!',
    'niceselect'
], function ($) {
    "use strict";

    $('select').niceSelect();

    //stick header
    var previousScroll = 0,
        pageHeader = $('header.page-header');
    $(window).scroll(function () {
        var currentScroll = $(this).scrollTop();
        if (currentScroll < previousScroll) {
            if (currentScroll > pageHeader.outerHeight()) {
                pageHeader.addClass('sticky-header');

            } else {
                pageHeader.removeClass('sticky-header');
            }
        } else {
            pageHeader.removeClass('sticky-header');
        }
        previousScroll = currentScroll;
    })
});
