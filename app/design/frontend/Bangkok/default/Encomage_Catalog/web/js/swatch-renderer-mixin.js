define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {
        $.widget('mage.SwatchRenderer', widget, {
            _EventListener: function () {
                var $widget = this,
                    options = this.options.classes,
                    target;
                $widget.element.on('click', '.' + options.optionClass, function () {
                    return $widget._OnClick($(this), $widget);
                });
                $widget.element.on('emulateClick', '.' + options.optionClass, function () {
                    return $widget._OnClick($(this), $widget, 'emulateClick');
                });
                $widget.element.on('change', '.' + options.selectClass, function () {
                    return $widget._OnChange($(this), $widget);
                });
                $widget.element.on('click', '.' + options.moreButton, function (e) {
                    e.preventDefault();
                    return $widget._OnMoreClick($(this));
                });
                $widget.element.on('keydown', function (e) {
                    if (e.which === 13) {
                        target = $(e.target);

                        if (target.is('.' + options.optionClass)) {
                            return $widget._OnClick(target, $widget);
                        } else if (target.is('.' + options.selectClass)) {
                            return $widget._OnChange(target, $widget);
                        } else if (target.is('.' + options.moreButton)) {
                            e.preventDefault();

                            return $widget._OnMoreClick(target);
                        }
                    }
                });

                var productColorData = this.options.jsonSwatchConfig.productColorData;
                if (productColorData || typeof productColorData !== 'undefined') {
                    $('div#option-label-color-' + productColorData.colorId + '-item-' + productColorData.colorValue + '').trigger('click');
                }
            }
        });

        return $.mage.SwatchRenderer;
    }
});