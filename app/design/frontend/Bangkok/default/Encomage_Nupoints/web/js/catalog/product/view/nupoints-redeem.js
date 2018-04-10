define([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/url'
], function ($, customerData, urlBuilder) {
    'use strict';
    return function (config, element) {
        function initUi(customerDataObject) {
            var customerNupointsValue = (customerDataObject.value) ? customerDataObject.value : 0;
            $element.find('.js-customer-nupoints-balance').html(customerNupointsValue);
            if (!customerDataObject.is_can_redeem) {
                $element.find('.js-customer-nuponts-redeem').attr('disabled', true);
            }
        }
        var $element = $(element),
            customerNupoints = customerData.get('nupoints')(),
            select = $element.find('.js-nupoints-rates'),
            redeemButton = $element.find('.js-customer-nuponts-redeem'),
            expiredSectionNames = customerData.getExpiredSectionNames();

        customerData.get('nupoints').subscribe(function (updatedCart) {
            initUi(updatedCart);
        }, this);

        if (expiredSectionNames.length > 0){
            expiredSectionNames.forEach(function (index, value) {
                if (index == 'customer-data') customerData.reload(['nupoints'], false);
            });
        }

        if (!customerNupoints.data_id) {
            customerData.reload(['nupoints'], false);
        } else {
            initUi(customerNupoints);

        }

        redeemButton.on('click', function () {
            var redeemNupoints = select.find('.js-selected-option-container').data('selectValue');
            $.ajax({
                url: urlBuilder.build(config.nupointsRedeemUrl),
                data: {'redeem_nupoints': redeemNupoints},
                type: 'post',
                dataType: 'json',
                async: false,
                showLoader: true,
                success: function (response) {
                    if (response.customer_nupoints && response.success) {
                        $element.find('.js-customer-nupoints-balance').html(response.customer_nupoints);
                    }
                }
            });
            $element.find('.js-customer-nuponts-redeem').attr('disabled', true);
        });
    }
});
