define([
    'jquery'
], function ($) {
    "use strict";

    $.widget('encomage.locatorWidgetDependency', {
        options: {
            ajaxUrl: '',
            widgetId: '',
            widgetCode: '',
            markersSelect: '',
            storeSelector: '',
            centerMarkerLabel: '',
            centerMarkerSelector: '',
            selectedCenterMarker: ''
        },
        /**
         *
         * @private
         */
        _create: function () {
            this._init();
            this._events();
        },
        /**
         *
         * @private
         */
        _init: function () {
            this.options.markersSelect = $("select[name*='parameters[markers][]']");
            this.options.storeSelector = $("#store_ids");
            this.options.centerMarkerSelector = $("select[name*='parameters[center_marker]']");
        },
        /**
         *
         * @private
         */
        _events: function () {
            var _this = this;
            $(document).ready(function () {
                if (!_this.options.storeSelector.val()) {
                    _this._lockedMarkerSelect();
                }
            });
            this.options.markersSelect.change(function () {
                _this._updateCenterMarkerSelector(($(this).val()) ? $(this).val() : []);
            });
            this.options.storeSelector.change(function () {
                if ($(this).val()) {
                    _this._updateMarkersAjax($(this).val());
                } else {
                    _this._lockedMarkerSelect();
                }
            })
        },

        /**
         *
         * @param markers
         * @private
         */
        _updateCenterMarkerSelector: function (markers) {
            var _this = this;
            this.options.centerMarkerSelector.empty();
            var centerMarkerOptions = '<option value="0">' + this.options.centerMarkerLabel + '</option>';
            if (markers.length) {
                $.each(markers, function (k, v) {
                    var markerName = '',
                        markerId = '';
                    if (typeof v == "object") {
                        markerName = v.label;
                        markerId = v.value;
                    } else {
                        markerName = $("select[name*='parameters[markers][]'] option[value='" + v + "']").html();
                        markerId = v;
                    }
                    if (markerName) {
                        var selected = '';
                        if (_this.options.selectedCenterMarker == markerId) {
                            selected = 'selected = "selected"';
                        }
                        centerMarkerOptions += '<option ' +
                            'value="' + parseInt(markerId) + '" ' +
                            ' ' + selected + '>' + markerName + '</option>';
                    }
                });
            }
            this.options.centerMarkerSelector.append(centerMarkerOptions);
        },

        /**
         *
         * @param stores
         * @private
         */
        _updateMarkersAjax: function (stores) {
            var _this = this;
            $.ajax({
                showLoader: true,
                url: _this.options.ajaxUrl,
                data: {
                    code: _this.options.widgetCode,
                    stores: stores,
                    instance_id: _this.options.widgetId
                },
                type: "POST",
                dataType: 'json'
            }).done(function (response) {
                if (response.error) {
                    location.reload();
                } else {
                    _this._updateMarkersSelect(response.markers);
                }
            });
        },
        /**
         *
         * @param markers
         * @private
         */
        _updateMarkersSelect: function (markers) {
            var markerOptions = '',
                updateCenterMarker = [];
            this.options.markersSelect.empty();
            $.each(markers, function (i, v) {
                var optGroup = '<optgroup label="' + v.label + '">';
                for (var j = 0; j < v.value.length; j++) {
                    var marker = v.value[j],
                        selected = '';
                    if (marker.selected) {
                        selected = 'selected = "selected"';
                    }
                    optGroup += '<option value="'
                        + parseInt(marker.value) + '" ' + selected + ' >'
                        + marker.label +
                        '</option>';
                    updateCenterMarker.push({value: parseInt(marker.value), label: marker.label})
                }
                optGroup += '</optgroup>';
                markerOptions += optGroup;
            });
            if (markerOptions != '') {
                this.options.markersSelect.attr('disabled', false);
                this.options.centerMarkerSelector.attr('disabled', false);
                this.options.markersSelect.append(markerOptions);
                this._updateCenterMarkerSelector(updateCenterMarker);
            }
        },
        /**
         *
         * @private
         */
        _lockedMarkerSelect: function () {
            this.options.markersSelect
                .empty()
                .attr('disabled', true);
            this.options.centerMarkerSelector
                .empty()
                .attr('disabled', true);
        }
    });
    return $.encomage.locatorWidgetDependency;
});