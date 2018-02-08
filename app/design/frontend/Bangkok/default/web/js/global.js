define([
    'jquery',
    'domReady!',
    'niceselect',
    'sticky'
], function ($) {
    "use strict";

    //init selections
    //---------------------------------------------
    $('select').niceSelect();

    //sticky elements
    //---------------------------------------------
    $('.js-sticky-sidebar').sticky({topSpacing: 43, bottomSpacing: $('.page-footer').outerHeight()});
    $('header.page-header').sticky({zIndex: 9999});


    //Use for change image on click
    //---------------------------------------------
    $(document).on('click', '.js-revert-image-on-click', function () {
        var target = $($(this).data('target'));
        if (target) {
            target.attr('src', $(this).data('src'));
            $('.js-revert-image-on-click').removeClass('active');
            $(this).addClass('active');
        }
    });

    //Accordion
    //---------------------------------------------
    $(document).on('click', '.js-accordion', function () {
        var e = $(this),
            target = e.next();
        e.toggleClass('active');
        if (parseInt(target.css('max-height'))) {
            target.css('max-height', 0);
        } else {
            target.css('max-height', target.prop('scrollHeight') + 'px');
        }
    });
});
