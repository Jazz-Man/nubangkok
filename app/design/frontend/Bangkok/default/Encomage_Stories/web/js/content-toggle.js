define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";
    return function (config, element) {

        $('.js-show-more').on('click', function () {
            var e = $(this);
            $('.js-short-content-'+e.data('storyId')).hide();
            $('.js-full-content-'+e.data('storyId')).show();
            $('.js-story-image-'+e.data('storyId')).hide();
        });
    }
});