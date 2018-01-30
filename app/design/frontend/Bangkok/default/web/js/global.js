define([
    'jquery',
    'js/plugins/jquery.nice-select'
], function (jQuery) {
    "use strict";

    return function () {
        jQuery('select').niceSelect();
    };
});