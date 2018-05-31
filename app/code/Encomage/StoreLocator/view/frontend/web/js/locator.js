define([
    'jquery',
    'jquery/ui'
], function ($) {
    'use strict';
    $.widget('encomage.storeLocator', {
        options: {
            selector: '',
            defaultLng: '',
            defaultLat: '',
            defaultZoom: '',
            selectedMarkerZoom: '',
            markers: '',
            centerMarkerId: ''
        },
        _map: null,
        _placedMarkers: [],
        /**
         *
         * @private
         */
        _create: function () {
            if (this.options.selector) {
                this._event();
                this._mapInit();
                this._markerPlacement();
            }
        },
        /**
         *
         * @private
         */
        _mapInit: function () {
            this._map = new google.maps.Map(document.getElementById(this.options.selector), {
                zoom: parseInt(this.options.defaultZoom),
                center: this._prepareCoordinates(this.options.defaultLat, this.options.defaultLng)
            });
        },
        /**
         *
         * @private
         */
        _markerPlacement: function () {
            var _this = this;
            $.each(this.options.markers, function (i, v) {
                var markerCoordinates = _this._prepareCoordinates(v.latitude, v.longitude);
                _this._placedMarkers[i] = new google.maps.Marker({
                    position: markerCoordinates,
                    map: _this._map
                });
                if (v.comment) {
                    _this._placedMarkers[i].info = new google.maps.InfoWindow({
                        content: v.comment
                    })
                }
                google.maps.event.addListener(_this._placedMarkers[i], 'click', function () {
                    _this._placedMarkers[i].info.open(_this._map, _this._placedMarkers[i]);
                });
                if (_this.options.centerMarkerId && _this.options.centerMarkerId == v.entity_id) {
                    _this._map.setCenter(markerCoordinates);
                }
            });
        },
        /**
         *
         * @private
         */
        _event: function () {
            var _this = this;
            $('.js-show-marker').on('click', function () {
                var marker = _this.options.markers[$(this).data('markerId')];
                _this._map.setZoom(parseInt(_this.options.selectedMarkerZoom));
                _this._map.setCenter(_this._prepareCoordinates(marker.latitude, marker.longitude));
            });
        },
        /**
         *
         * @param lat
         * @param lng
         * @returns {google.maps.LatLng}
         * @private
         */
        _prepareCoordinates: function (lat, lng) {
            return new google.maps.LatLng(parseFloat(lat), parseFloat(lng))
        }

    });
    return $.encomage.storeLocator;
});