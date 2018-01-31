define([
    'jquery',
    'js/plugins/jquery.nice-select'
], function ($) {
    "use strict";

    return function () {
        $('select').niceSelect();
    };
});