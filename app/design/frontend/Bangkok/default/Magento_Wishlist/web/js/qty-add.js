define([
        'jquery'
    ], function ($) {
        var i;
        $(".qty-enhance").click(function () {
            i = $(this).parent().find('input').val();
            $(this).parent().find('input').val(++i);
        });
        $(".qty-reduce").click(function () {
            i = $(this).parent().find('input').val();
            if (--i > 0) {
                return $(this).parent().find('input').val(i);
            }
        });
    }
);