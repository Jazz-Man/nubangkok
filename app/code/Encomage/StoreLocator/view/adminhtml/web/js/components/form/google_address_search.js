define([
    "jquery",
    "Magento_Ui/js/form/element/abstract",
    "Magento_Ui/js/modal/alert",
    "ko"
], function ($, Abstract, alert, ko) {
    return Abstract.extend({
        valueLat: ko.observable(''),
        valueLng: ko.observable(''),
        valueSearch: ko.observable(''),
        _defaultZoom: 8,
        _selectedMarkerZoom: 20,
        _dbClickZoom: 12,
        _zoomIterator: 1,
        _map: null,
        _marker: null,
        _autocomplete: null,
        _infoWindow: null,
        _defaultLatLng: {
            lat: '47.795770',
            lng: '35.202652'
        },

        /**
         * Construct
         */
        onElementRender: function () {
            this._init();
            this._initFieldsValue();
            this._events();

        },
        /**
         * button click event
         */
        clickShowMarkerOnMap: function () {
            this._updateMarker(new google.maps.LatLng(this.valueLat(), this.valueLng()));
            this.value(this.valueLat() + ':' + this.valueLng())
        },
        /**
         * Set value coordinates fields
         * @private
         */
        _initFieldsValue: function () {
            var data = this._getUnSplitCoordinates();
            if (data) {
                this.valueLat(data[0]);
                this.valueLng(data[1]);
            }
        },

        /**
         * Prepare coordinates data
         * @returns {*}
         * @private
         */
        _getUnSplitCoordinates: function () {
            if (this.initialValue) {
                var data = this.initialValue.split(':');
                if (data.length == 2) {
                    return data;
                }
            }
            return null;
        },

        /**
         *
         * @private
         */
        _events: function () {
            var _this = this;
            this._autocomplete.addListener('place_changed', function () {
                _this._infoWindow.close();
                var place = _this._autocomplete.getPlace();
                if (!place.geometry) {
                    alert({
                        content: "Autocomplete's returned place contains no geometry"
                    });
                }
                if (place.geometry.viewport) {
                    _this._map.fitBounds(place.geometry.viewport);
                } else {
                    _this._map.setCenter(place.geometry.location);
                    _this._map.setZoom(_this._defaultZoom);
                }
                _this._updateMarker(place.geometry.location);
                _this._updateFieldsParams(place.geometry.location.lat(), place.geometry.location.lng());
            });
            this._map.addListener('dblclick', function (e) {
                var currentZoom = _this._map.getZoom();
                if (currentZoom < _this._dbClickZoom) {
                    _this._map.setZoom(_this._dbClickZoom);
                }
                _this._updateMarker(e.latLng);
                _this._updateFieldsParams(e.latLng.lat(), e.latLng.lng());
            });
            this._marker.addListener('click', function () {
                _this._map.setZoom(_this._selectedMarkerZoom);
                _this._map.setCenter(_this._marker.getPosition());
            });
        },
        /**
         *
         * @param location
         * @private
         */
        _updateMarker: function (location) {
            this._marker.setVisible(false);
            this._marker.setPosition(location);
            this._map.setCenter(location);
            this._marker.setVisible(true);
        },
        /**
         *
         * @private
         */
        _init: function () {
            this._infoWindow = new google.maps.InfoWindow();
            this._map = new google.maps.Map(document.getElementById('google-address-search-map'), {
                zoom: this._defaultZoom,
                center: new google.maps.LatLng(this._defaultLatLng.lat, this._defaultLatLng.lng)
            });
            this._marker = new google.maps.Marker({
                map: this._map,
                anchorPoint: new google.maps.Point(0, -29)
            });
            this._autocomplete = new google.maps.places.Autocomplete(
                (document.getElementById('google-address-search')),
                {types: ['geocode']}
            );
            var data = this._getUnSplitCoordinates();
            if (data) {
                this.valueLat(data[0]);
                this.valueLng(data[1]);
                this._updateMarker(new google.maps.LatLng(data[0], data[1]));
                this._updateFieldsParams(data[0], data[1]);
            }
        },
        /**
         * Update params in coordinates fields
         * @param lat
         * @param lng
         * @private
         */
        _updateFieldsParams: function (lat, lng) {
            this.valueLat(lat);
            this.valueLng(lng);
            this.value(lat + ':' + lng)
        }
    })
});