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
            if ($('.desktop-change-position-right').has('iframe').length > 0) {
                $('.js-google-map').remove();
                $('.desktop-change-position-right').prepend('<img id="myImage" src="' + $(this).data("src") + '" alt="" />');
            }
            target.attr('src', $(this).data('src'));
            $('.js-revert-image-on-click').removeClass('active');
            $(this).addClass('active');
        } else {
            if ($('.desktop-change-position-right').has('img#myImage').length > 0) {
                target.remove();
                $('.desktop-change-position-right').prepend('<iframe class="js-google-map" src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15502.62769014799!2d100.5552439!3d13.739209!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x24c438254d55b574!2zTlUgQmFuZ2tvayBIZWFkIG9mZmljZSDguJou4LiZ4Li44LmJ4LiiIOC4iOC4s-C4geC4seC4lA!5e0!3m2!1sen!2sth!4v1524228920680" width="600" height="450" frameborder="0" style="border:0" allowfullscreen></iframe>')
            }
        }
        $('.cms-block').hide();
        (cmsBlockContent) ? cmsBlockContent.show() : cmsBlockContent.hide();
    });
});
