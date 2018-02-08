define([
        'jquery'
    ], function ($) {
        return function () {
            $("#customer-show-password").click(function () {
                var element = $(this);
                if (element.prop("checked")) {$("#pass").prop("type", "text");}
                else {$("#pass").prop("type", "password");}
            })
        }
    }
);