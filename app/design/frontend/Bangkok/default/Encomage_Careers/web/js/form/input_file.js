define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";
    return function (config, element) {
        function setProperty(selector, name, size) {
            if (name && size) {
                $(selector + '-name').text(name);
                $(selector + '-size').text(size);
            } else {
                $(selector + '-name').empty();
                $(selector + '-size').empty();
            }
        }

        $('.js-input-cv').on('change', function () {
            var cv = $(this).prop('files')[0];
            if (cv) {
                setProperty('.js-cv-file', cv.name.replace(/.*\\/, ""), cv.size + ' KB');
            } else {
                setProperty('.js-cv-file', null, null);
            }
        });
        $('.js-cv-file-unset').on('click', function () {
            var cv = $('.js-input-cv');
            if (cv.prop('files')[0]) {
                cv.val('');
                setProperty('.js-cv-file', null, null);
            }
        });

        $('.js-input-photo').on('change', function () {
            var photo = $(this).prop('files')[0];
            if (photo) {
                setProperty('.js-photo-file', photo.name.replace(/.*\\/, ""), photo.size + ' KB');
            } else {
                setProperty('.js-photo-file', null, null);
            }
        });
        $('.js-photo-file-unset').on('click', function () {
            var photo = $('.js-input-photo');
            if (photo.prop('files')[0]) {
                photo.val('');
                setProperty('.js-photo-file', null, null);
            }
        });
    }
});