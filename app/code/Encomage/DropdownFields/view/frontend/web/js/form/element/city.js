/**
 * @api
 */
define([
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
], function (_, registry, Select, defaultPostCodeResolver, $) {
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
                parentCity.hide();
                return value;
            }

            var regionCities,
                cityOptions =[],
                regionData = registry.get(this.parentName + '.' + 'region_id').indexedOptions[value];

            var url = this.urlApiCity + regionData.country_id + '/search/?region=' + regionData.label + '&key=' + this.apiKey;
            $.ajax({
                async: false,
                url: url
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

            var getCity = this.parentName + '.' + 'city_id',
                city = registry.get(getCity),
                cases = cityOptions.length;

            if (cases === 0) {
                city.hide();
                $("[name ='shippingAddress.region']").hide();
                parentCity.show();
            } else {
                city.show();
                this.show();
                parentCity.hide();
            }
        }
    });
});