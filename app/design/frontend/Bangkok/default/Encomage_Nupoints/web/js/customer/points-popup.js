define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";

    //-------------- left points block
    $(document).on('click', '.js-open-left', function () {
        $('.float-left-content').addClass('active');
    });
    $(document).on('click', '.js-close-left', function () {
        $('.float-left-content').removeClass('active');
    });


    //-------------- right points block
    $(document).on('click', '.js-open', function () {
        $('.float-right-content').addClass('active');
    });
    $(document).on('click', '.js-close', function () {
        $('.float-right-content').removeClass('active');
    });
});
