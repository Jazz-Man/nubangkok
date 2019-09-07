define([
    "jquery",
    "mage/translate",
    "jquery/ui",
    'domReady!'
], function ($, $t) {
    "use strict";

    return function (config, element) {
        var attributes = config.attributes.split(","),
            stockData = config.stockData,
            ajaxUrl = config.ajaxUrl;

        $("#encomage_product_notify_button").on('click', function () {
            var dataForm = $('#product-notification');
            dataForm.mage('validation', {});
            if (dataForm.valid()) {
                event.preventDefault();
                return _sendNotify(dataForm.serialize());
            }
        });

        $.each(attributes, function (index, value) {
            $(document).on('click', '.swatch-attribute.' + value, function () {
                checkProductInfo();
            });
        });

        var _sendNotify = function (param) {
            $.ajax({
                showLoader: true,
                url: ajaxUrl,
                data: param,
                type: "POST"
            }).done(function (data) {
                $("#product-notification").trigger("reset");
                $('.notice').html($t('We will notify you as soon as the product is available.'));
                return true;
            });
        };

        var checkProductInfo = function () {
            var productOptions = {},
                outOfStockItems = stockData.outOfStockItems,
                showNotify = false;

            $.each(attributes, function (index, value) {
                var swatch = $('.swatch-attribute.' + value),
                    optionVal = swatch.attr("option-selected");

                if (typeof optionVal !== typeof undefined && optionVal !== false) {
                    productOptions[value] = optionVal;
                } else {
                    for (var key in productOptions) {
                        if (key == value) {
                            delete productOptions[value];
                        }
                    }
                    showNotify = false;
                }
                if (attributes.length == Object.values(productOptions).length) {
                    $.each(outOfStockItems, function (productId, value) {
                        if (JSON.stringify(value.options) == JSON.stringify(productOptions)) {
                            showNotify = true;
                            return true;
                        }
                    });
                }
            });
            if (showNotify) {
                $('.product-notification-contact-form').show();
                $('.product-options-bottom').hide();
                $('.field.qty').hide();
            } else {
                $('.product-notification-contact-form').hide();
                $('.product-options-bottom').show();
                $('.field.qty').show();
            }
        };
    };
});