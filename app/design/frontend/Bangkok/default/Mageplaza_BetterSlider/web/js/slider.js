define([
    'jquery',
    'matchMedia',
    'domReady!',
    'owlcarousel',
    'Mageplaza_BetterSlider/js/owl.navigation',
    'Mageplaza_BetterSlider/js/owl.video',
    'Mageplaza_BetterSlider/js/owl.autoplay'
], function ($, mediaCheck) {
    "use strict";

    return function (config, element) {
        $(element).owlCarousel(config);
        var desktopItems = $('.js-slider-image *[data-slider-desktop-img]'),
            mobileItems = $('.js-slider-image *[data-slider-mobile-img]');
        mediaCheck({
            media: '(max-width: 768px)',
            entry: $.proxy(function () {

                // mobile
                // ----------------------------------------------------------------
                mobileItems.show();
                desktopItems.hide();


            }, this),
            exit: $.proxy(function () {

                // desktop
                // ----------------------------------------------------------------
                mobileItems.hide();
                desktopItems.show();

            }, this)
        });
    };
});