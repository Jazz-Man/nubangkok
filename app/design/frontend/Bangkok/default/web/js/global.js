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
        sidebar = $('.sidebar.sidebar-additional'),
        baseSidebarMargin = parseInt(sidebar.css('margin-top')),
        mainContentHeight = $('#maincontent').innerHeight();
    if ($(document).scrollTop()) {
        sidebar.css('margin-top', baseSidebarMargin + $(document).scrollTop());
    }
    $(window).scroll(function () {
        var currentScroll = $(this).scrollTop();
        if (currentScroll < previousScroll) {
            //top
            if (currentScroll > pageHeader.outerHeight()) {
                pageHeader.addClass('sticky-header');
                //sidebar.css('margin-top', baseSidebarMargin + pageHeader.outerHeight());

            } else {
                pageHeader.removeClass('sticky-header');
                //sidebar.css('margin-top', baseSidebarMargin);

            }
            sidebar.css('margin-top', baseSidebarMargin + currentScroll);
        } else {
            //bottom
            pageHeader.removeClass('sticky-header');
            //sidebar.css('margin-top', baseSidebarMargin);
            if (currentScroll < mainContentHeight) {
                sidebar.css('margin-top', baseSidebarMargin + currentScroll);

            }

        }
        previousScroll = currentScroll;
    })
});
