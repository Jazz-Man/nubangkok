define([
    'jquery',
    'domReady!',
    'owlcarousel',
    'Mageplaza_BetterSlider/js/owl.navigation',
    'Mageplaza_BetterSlider/js/owl.video',
    'Mageplaza_BetterSlider/js/owl.autoplay'
], function ($) {
    "use strict";

    return function (config, element) {
        $(element).owlCarousel(config);
    };
});