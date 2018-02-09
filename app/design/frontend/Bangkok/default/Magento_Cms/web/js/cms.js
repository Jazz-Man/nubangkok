define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";

    //used on where-to-buy CMS page
    //---------------------------------------------
    $(document).on('click', '.js-revert-image-on-click', function () {
        var target = $($(this).data('target')),
            gmtBtn = $('.js-show-gm-btn');
        if ($(this).data('gmpBtn')) {
            gmtBtn.show();
        } else {
            gmtBtn.hide();
        }
        if (target) {
            target.attr('src', $(this).data('src'));
            $('.js-revert-image-on-click').removeClass('active');
            $(this).addClass('active');
        }
    });
});
