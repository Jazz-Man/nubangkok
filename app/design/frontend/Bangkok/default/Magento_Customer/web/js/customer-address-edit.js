define([
    'jquery',
    'accordion'
], function ($) {
    "use strict";
    return function (config, element) {
        element = $(element);
        element.accordion();
        element.find('.js-edit').on('click', function () {
            var a = $(this);
            if (a.hasClass('edit')) {
                a.removeClass('edit').addClass('save').find('span').text($.mage.__('Save Address'));
            } else if (a.hasClass('save')) {
                element.find('form').submit();
            }
        });
        $('#use_default_address').on('change', function () {
            if (this.checked) {
                var billingCountryValue = $('.box-billing-address select[name="country_id"]').find(":selected").val(),
                    shippingCountrySelect = $('.box-shipping-address select[name="country_id"]'),
                    streetBilling = $('.box-billing-address .street input'),
                    streetShipping = $('.box-shipping-address .street input');
                shippingCountrySelect.find('option[value="' + billingCountryValue + '"]').attr('selected', 'selected');
                shippingCountrySelect.niceSelect('update');
                streetBilling.each(function (index, field) {
                    if (field.value) {
                        streetShipping[index].value = field.value;
                    }
                });
                $('.box-shipping-address .city input').val($('.box-billing-address .city input').val());
                $('.box-shipping-address .zip input').val($('.box-billing-address .zip input').val());
                $('.box-shipping-address input[name="telephone"]').val($('.box-billing-address input[name="telephone"]').val());
            }
        })
    };
});