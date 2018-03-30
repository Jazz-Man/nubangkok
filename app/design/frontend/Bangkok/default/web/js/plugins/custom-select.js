define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function ($, $t) {
    'use strict';

    $.widget('encomage.customSelect', {

        list: false,
        options: {placeholder: false},
        optionsList: false,
        isListOpened: false,
        selectedOption: false,
        selectedOptionContainer: false,


        /** @inheritdoc */
        _create: function () {
            this.list = this.element.find('.js-list');
            this.optionsList = this.list.find('.js-option');
            this.list.prepend($('<span class="js-selected-option-container placeholder"></span>'));
            this.selectedOptionContainer = this.element.find('.js-selected-option-container');
            this.selectedOptionContainer.html(this.options.placeholder);
            this._initListing();

        },
        _initListing: function () {
            var self = this;
            this._hideOptions();
            this.list.on('click', function () {
                if (self.isListOpened) {
                    self._hideOptions();
                } else {
                    self._showOptions();
                }
            });
            this.optionsList.each(function (index, value) {
                $(value).on('click', function () {
                    self.onOptionClick($(this));
                    return;
                });
            })
        },

        onOptionClick: function (e) {
            this.selectedOption = e;
            this._hideOptions();
            this.selectedOptionContainer.addClass('js-option-selected');
            this.selectedOptionContainer.attr('data-select-value', this.selectedOption.data('optionValue'));
            this.list.css('height', e.height());
            this.selectedOptionContainer.html(this.selectedOption.html())
        },

        _hideOptions: function () {
            this.optionsList.hide();
            this.element.removeClass('active');
            this.isListOpened = false;
        },
        _showOptions: function () {
            this.optionsList.show();
            this.element.addClass('active');
            this.isListOpened = true;
        },
        value: function () {
            return this.selectedOptionContainer.data('selectValue');
        }
    });
    return $.encomage.customSelect;
});
