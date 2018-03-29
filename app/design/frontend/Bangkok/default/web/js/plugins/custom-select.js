define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function ($, $t) {
    'use strict';

    $.widget('encomage.customSelect', {

        list: false,
        options: false,
        isListOpened: false,
        selectedOption: false,
        selectedOptionContainer: false,


        /** @inheritdoc */
        _create: function () {
            var self = this;
            this.list = this.element.find('.js-list');
            this.options = this.list.find('.js-option');

            this._initListing();

        },
        _initListing: function () {
            var self = this;
            this._hideOptions();
            this.list.css({'width': '80%', 'height': '20px', 'border': '1px solid black'});
            this.list.on('click', function () {
                if (self.isListOpened) {
                    self._hideOptions();
                } else {
                    self._showOptions();
                }
            });
            this.options.each(function (index, value) {
                $(value).css('margin-top', self.list.height());
                $(value).on('click', function () {
                    self.onOptionClick($(this));
                    return;
                });
                $(value).on('hover', function () {
                    $(this).addClass('hover');
                });
                $(value).on('mouseleave', function () {
                    $(this).removeClass('hover');
                })
            })
        },

        onOptionClick: function (e) {
            this.selectedOption = e;
            this._hideOptions();
            if (!this.selectedOptionContainer) {
                this.element.prepend($('<div class="js-selected-option-container"></div>'));
                this.selectedOptionContainer = this.element.find('.js-selected-option-container');
            }
            this.selectedOptionContainer.attr('data-select-value', this.selectedOption.data('optionValue'));
            this.selectedOptionContainer.html(this.selectedOption.html())
        },

        _hideOptions: function () {
            this.options.hide();
            this.isListOpened = false;
        },
        _showOptions: function () {
            this.options.show();
            this.isListOpened = true;
        },
        value: function () {
            return this.selectedOptionContainer.data('selectValue');
        }
    });

    return $.encomage.customSelect;
});
