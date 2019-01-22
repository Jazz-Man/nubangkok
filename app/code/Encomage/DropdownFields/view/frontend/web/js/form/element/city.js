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
            if (value === "") {
                this.hide();
                return value;
            }

            var parentCity,
                regionCities,
                cityOptions =[],
                regionData = registry.get(this.parentName + '.' + 'region_id').indexedOptions[value];

            //TODO Take out the key in the settings
            var url = 'https://battuta.medunes.net/api/city/' + regionData.country_id + '/search/?region=' + regionData.label + '&key=7fc58e98faad8af9bc6ca81e6384b87a';
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

            var getCity = this.parentName + '.' + 'city',
                city = registry.get(getCity),
                cases = cityOptions.length;

            parentCity = $("[name ='shippingAddress.city']");
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