define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";

    //-------------- left points block
    $(document).on('click touchstart', '.js-open-left', function () {
        $('.float-left-content').addClass('active');
    });
    $(document).on('click touchstart', '.js-close-left', function () {
        $('.float-left-content').removeClass('active');
    });


    //-------------- right points block
    $(document).on('click touchstart', '.js-open', function () {
        $('.float-right-content').addClass('active');
    });
    $(document).on('click touchstart', '.js-close', function () {
        $('.float-right-content').removeClass('active');
    });


    //
    // points blocks height 
    //
    function equivalentHeight () {
        let left = $('.points-container .left-block .content'),
            right = $('.points-container .right-block .content'),
            rightH = right.height();

            left.height(rightH);
    }
    equivalentHeight();

    $(window).resize(function() {
        equivalentHeight();
    });

});
