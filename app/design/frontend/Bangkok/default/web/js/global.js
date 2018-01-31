define([
    'jquery',
    'domReady!',
    'niceselect'
], function ($) {
    "use strict";

    //init selections
    //---------------------------------------------
    $('select').niceSelect();

    //stick header
    //---------------------------------------------
    var previousScroll = 0,
        pageHeader = $('header.page-header'),
        sidebar =  $('.sidebar.sidebar-additional'),
        baseSidebarMargin = parseInt(sidebar.css('margin-top'));
    $(window).scroll(function () {
        var currentScroll = $(this).scrollTop();
        if (currentScroll < previousScroll) {
            if (currentScroll > pageHeader.outerHeight()) {
                pageHeader.addClass('sticky-header');
                sidebar.css('margin-top', baseSidebarMargin + pageHeader.outerHeight());

            } else {
                pageHeader.removeClass('sticky-header');
                sidebar.css('margin-top', baseSidebarMargin);
            }
        } else {
            pageHeader.removeClass('sticky-header');
            sidebar.css('margin-top', baseSidebarMargin);
        }
        previousScroll = currentScroll;
    })
});
