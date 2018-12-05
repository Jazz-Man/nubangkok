define([
    'jquery',
    'domReady!'
], function ($) {
    "use strict";

    if ($('.js-revert-image-on-click').hasClass('active')) {
        var firstBlock = $('.js-revert-image-on-click').data('cmsBlock'),
            firstBlockCont = $(firstBlock);
        firstBlockCont.show();
    }

    //used on where-to-buy CMS page
    //---------------------------------------------
    var map = $('#widget-map');
    if (map.length) {
        map = map[0].firstElementChild;
    }
    $(document).on('click', '.js-revert-image-on-click', function () {
        var target = $($(this).data('target')),
            gmtBtn = $('.js-show-gm-btn'),
            cmsBlock = $(this).data('cmsBlock'),
            cmsBlockContent = $(cmsBlock);
        if ($(this).data('gmpBtn')) {
            gmtBtn.show();
        } else {
            gmtBtn.hide();
        }
        if (target && cmsBlock !== '.shopping-maills') {
            $('.widget-store-locator').remove();
            if ($('.desktop-change-position-right').has('img#myImage').length == 0) {
                $('.desktop-change-position-right').prepend('<img id="myImage" src="' + $(this).data("src") + '" alt="" />');
            }
            target.attr('src', $(this).data('src'));
            $('.js-revert-image-on-click').removeClass('active');
            $(this).addClass('active');
        } else {
            if ($('.desktop-change-position-right').has('img#myImage').length > 0) {
                target.remove();
                $('.desktop-change-position-right').prepend(map)
            }
        }
        $('.cms-block').hide();
        (cmsBlockContent) ? cmsBlockContent.show() : cmsBlockContent.hide();
    });
});
