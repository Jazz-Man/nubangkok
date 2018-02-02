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
    $('header.page-header').sticky({zIndex:9999});
});
