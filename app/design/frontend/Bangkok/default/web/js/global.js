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

    //sticky elements
    //---------------------------------------------
    $('.js-sticky-sidebar').sticky({topSpacing: 43});
    $('header.page-header').sticky({zIndex:9999});


  /*  var previousScroll = 0,
        pageHeader = $('header.page-header'),
        sidebar = $('.sidebar.sidebar-additional');
    $(window).scroll(function () {
        var currentScroll = $(this).scrollTop();
        if (currentScroll < previousScroll) {
            //top
            if (currentScroll > pageHeader.outerHeight()) {
                pageHeader.addClass('sticky-header');

            } else {
                pageHeader.removeClass('sticky-header');

            }
        } else {
            //bottom
            pageHeader.removeClass('sticky-header');
        }
        previousScroll = currentScroll;
    })*/
});
