define([
        'jquery'
    ], function ($) {
    var i;
        $(".qty-enhance").click(function () {
            i = $(this).parent().find('input').val();
            console.log($(this).parent().find('input').val(++i));
        });
        $(".qty-reduce").click(function () {
            i = $(this).parent().find('input').val();
            console.log($(this).parent().find('input').val(--i));
        });
    }
);