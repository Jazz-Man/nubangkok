/**
 * @api
 */
define([
    'jquery',
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver'
], function ($, _, registry, Select, defaultPostCodeResolver) {
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
                    var url = this.urlApiRegion + value + '/all/?key=' + this.apiKey;
                    $.ajax({
                        async: false,
                        url: url
                    }).done(function (data) {
                        var regionsData = data;
                        regionsData.forEach(function (item, i) {
                            var jsonObject = {
                                country_id: item.country.toUpperCase(),
                                label: item.region,
                                labeltitle: item.region,
                                title: item.region,
                                value: i + 600,
                            };
                            self.initialOptions.push(jsonObject);
                        });
                    });
                    this._super(value, field);
                }
            }
        }
    });
});
