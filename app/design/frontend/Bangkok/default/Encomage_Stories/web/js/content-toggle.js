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
            $('.js-share-story-'+e.data('storyId')).show();
        });

        $('.js-show-less').on('click', function () {
            var e = $(this);
            $('.js-short-content-'+e.data('storyId')).show();
            $('.js-full-content-'+e.data('storyId')).hide();
            $('.js-share-story-'+e.data('storyId')).hide();
        })
    }
});