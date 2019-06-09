/**
 * @api
 */
define([
    'mage/url',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'jquery/ui'
], function (url,_, registry, Select, defaultPostCodeResolver, $) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.region_id:value'
            }
        },

        update: function (value) {
            var parentCity = $("[name ='shippingAddress.city']");
            if (value === "" || value === undefined ) {
                return value;
            }

            var regionCities,
                cityOptions =[],
                regionData = registry.get(this.parentName + '.' + 'region_id').indexedOptions[value];

            $.ajax({
                async: false,
                url: url.build('encomage_dropdownField/index/index'),
                type:"POST",
                data:{type:'city',country_code:regionData.country_id,region_label:regionData.label},
            }).done(function (data) {
                regionCities = data;
            });

            var i =1;
            for (var item in regionCities) {

                var jsonObject = {
                    country_id:regionData.country_id,
                    label: regionCities[item],
                    labeltitle: regionCities[item],
                    title: regionCities[item],
                    value: i + 600,
                };
                cityOptions.push(jsonObject);
                i++;
            }
            this.setOptions(cityOptions);
            var cases = cityOptions.length;

            if (cases === 0) {
                this.hide();
            } else {
                this.show();
            }
        }
    });
});