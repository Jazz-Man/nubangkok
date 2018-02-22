define([
    'jquery',
    'domReady!',
    'niceselect',
    'sticky'
], function ($) {
    "use strict";

    //variables
    //---------------------------------------------
    var $pageHeader = $('header.page-header'), $sidebarAdditionalEl = $('.sidebar.sidebar-additional');

    //init selections
    //---------------------------------------------
    $('select').niceSelect();

    $sidebarAdditionalEl.css('min-height', $('.columns').outerHeight());

    $('.js-sidebar-categories').on('sub-menu-show', function (e) {
        $sidebarAdditionalEl.css('min-height', $('.left-sidebar-container.js-sticky-sidebar').outerHeight());
    });

    //sticky elements
    //---------------------------------------------
    $('.js-sticky-sidebar').sticky({
        topSpacing: $pageHeader.outerHeight() + 5,
        bottomSpacing: $('.page-footer').outerHeight()
    });
    $pageHeader.sticky({zIndex: 9999});


    //Use for change image on click
    //included on where-to-buy CMS page
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
