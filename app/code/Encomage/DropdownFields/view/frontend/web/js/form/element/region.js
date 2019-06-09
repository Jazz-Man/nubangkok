/**
 * @api
 */
define([
    'mage/url',
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver'
], function (url, $, _, registry, Select, defaultPostCodeResolver) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.country_id:value'
            }
        },

        update: function (value) {
            var country = registry.get(this.parentName + '.' + 'country_id'),
                options = country.indexedOptions,
                isRegionRequired,
                option;

            if (!value) {
                return;
            }
            option = options[value];

            if (typeof option === 'undefined') {
                return;
            }
            defaultPostCodeResolver.setUseDefaultPostCode(!option['is_zipcode_optional']);
            if (this.skipValidation) {
                this.validation['required-entry'] = false;
                this.required(false);
            } else {
                if (option && !option['is_region_required']) {
                    this.error(false);
                    this.validation = _.omit(this.validation, 'required-entry');
                } else {
                    this.validation['required-entry'] = true;
                }

                if (option && !this.options().length) {
                    registry.get(this.customName, function (input) {
                        isRegionRequired = !!option['is_region_required'];
                        input.validation['required-entry'] = isRegionRequired;
                        input.required(isRegionRequired);
                    });
                }
                this.required(!!option['is_region_required']);
            }
        },

        filter: function (value, field) {
            var country = registry.get(this.parentName + '.' + 'country_id');
            if (country) {
                var self = this;
                if (value.trim() === "") {
                    this._super(value, field);
                } else {
                    $.ajax({
                        async: false,
                        type: "POST",
                        url: url.build('encomage_dropdownField/index/index'),
                        data: {type: 'region', country_code: value}
                    }).done(function (data) {
                        var regionsData = data, i = 1,
                            countryId =  this.data.split('=').pop();
                        for (var item in regionsData) {

                            var jsonObject = {
                                country_id: countryId,
                                label: regionsData[item],
                                labeltitle: regionsData[item],
                                title: regionsData[item],
                                value: i + 600,
                            };
                            self.initialOptions.push(jsonObject);
                            i++;
                        }
                    });
                    this._super(value, field);
                }
            }
        }
    });
});
