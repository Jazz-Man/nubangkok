define([
    'jquery',
    'domReady!',
    'niceselect',
    'sticky'
], function ($) {
    "use strict";

    //init selections
    //---------------------------------------------
    $('select').niceSelect();

    //sticky sidebar
    //---------------------------------------------
    $('.js-sticky-sidebar').sticky({topSpacing:100});

    //stick header
    //---------------------------------------------
    var previousScroll = 0,
        pageHeader = $('header.page-header'),
        sidebar = $('.sidebar.sidebar-additional'),
        baseSidebarMargin = parseInt(sidebar.css('margin-top'));
    if ($(document).scrollTop()) {
        sidebar.css('margin-top', baseSidebarMargin + $(document).scrollTop());
    }
    $(window).scroll(function () {
        var currentScroll = $(this).scrollTop();
        if (currentScroll < previousScroll) {
            //top
            if (currentScroll > pageHeader.outerHeight()) {
                pageHeader.addClass('sticky-header');

            } else {
                pageHeader.removeClass('sticky-header');

            }
            sidebar.css('margin-top', baseSidebarMargin + currentScroll);
        } else {
            //bottom
            pageHeader.removeClass('sticky-header');
        }
        previousScroll = currentScroll;
    })
});
