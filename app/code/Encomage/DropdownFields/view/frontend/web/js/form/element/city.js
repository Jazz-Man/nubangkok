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
            regionCities.forEach(function (item, i) {
                var jsonObject = {
                    value: i,
                    title: item.city,
                    country_id: "",
                    label: item.city
                };

                cityOptions.push(jsonObject);
            });
            this.setOptions(cityOptions);
            var getCity = this.parentName + '.' + 'city',
                city = registry.get(getCity),
                cases = cityOptions.length;

            if (cases === 0) {
                city.show();
                this.hide();
                parentCity.show();
            } else {
                city.hide();
                this.show();
                parentCity.hide();
            }
        }
    });
});