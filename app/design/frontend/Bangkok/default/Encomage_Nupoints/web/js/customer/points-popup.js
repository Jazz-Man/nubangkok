define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";

    //-------------- left points block
    $(document).on('click touchstart', '.js-open-left', function () {
        debugger;
        $('.float-left-content').addClass('active');
    });
    $(document).on('click touchstart', '.js-close-left', function () {
        $('.float-left-content').removeClass('active');
    });


    //-------------- right points block
    $(document).on('click touchstart', '.js-open', function () {
        debugger;
        $('.float-right-content').addClass('active');
    });
    $(document).on('click touchstart', '.js-close', function () {
        $('.float-right-content').removeClass('active');
    });
});
