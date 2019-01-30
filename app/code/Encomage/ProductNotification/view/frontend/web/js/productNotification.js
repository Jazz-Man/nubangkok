define([
    "jquery",
    "jquery/ui",
    "mage/translate"
], function ($,$t) {
    "use strict";

    function productNotification(config, element) {
        var dataForm = $('#product-notification');
        dataForm.mage('validation', {});

        $("#encomage_product_notify_button").on('click', function () {
            if (dataForm.valid()) {
                event.preventDefault();
                var param = dataForm.serialize(),
                    ajaxUrl = config.ajaxUrl;
                $.ajax({
                    showLoader: true,
                    url: ajaxUrl,
                    data: param,
                    type: "POST"
                }).done(function (data) {
                    document.getElementById("product-notification").reset();
                    $('.notice').html('We will notify you as soon as the product is available.');
                    return true;
                });
            }
        });
    };

    return productNotification;
});