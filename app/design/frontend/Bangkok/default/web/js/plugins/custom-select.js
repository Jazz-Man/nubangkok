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
        marginBottom: 0,
        listHeight: 0,
        noItem: false,

        /** @inheritdoc */
        _create: function () {
            this.list = $(document).find('.js-list');
            this.listHeight = this.list.height();
            this.optionsList = this.list.find('.js-option');
            this.list.prepend($('<div class="js-no-item"><span>' + this.options.placeholder + '</span></div>'));
            this.list.prepend($('<span class="js-selected-option-container placeholder"></span>'));
            this.noItem = this.list.find('.js-no-item');
            this.selectedOptionContainer = $(document).find('.js-selected-option-container');
            this.selectedOptionContainer.html(this.options.placeholder);
            this._initListing();

        },
        _initListing: function () {
            var self = this;
            this._hideOptions();
            this.selectedOptionContainer.on('click', function () {
                if (self.isListOpened) {
                    self._hideOptions();
                } else {
                    self._showOptions();
                }
            });
            this.optionsList.each(function (index, value) {
                $(value).on('click', function () {
                    self.onOptionClick($(this));
                });
                if ($(value).attr('selected')) {
                    self.onOptionClick($(value));
                }
                self.marginBottom += 65;
            });
            this.noItem.on('click', function () {
                self.selectedOptionContainer.removeClass('js-option-selected');
                self.selectedOptionContainer.removeAttr('data-select-value');
                self.selectedOptionContainer.html(self.options.placeholder);
                self.list.css('height', self.listHeight);
                self._hideOptions();
            });
        },

        onOptionClick: function (e) {
            this.selectedOption = e;
            this._hideOptions();
            this.selectedOptionContainer.addClass('js-option-selected');
            this.selectedOptionContainer.attr('data-select-value', this.selectedOption.data('optionValue'));
            this.list.css('height', e.height());
            $(document).find('.totals.redeem').css('height', e.height());
            this.selectedOptionContainer.html(this.selectedOption.html())
        },

        _hideOptions: function () {
            this.optionsList.hide();
            this.noItem.hide();
            this.element.removeClass('active');
            this.isListOpened = false;
            this.list.css('margin-bottom', 0);
            $(document).find('.totals.redeem').css('margin-bottom', 0);
        },
        _showOptions: function () {
            this.optionsList.show();
            if (this.selectedOptionContainer.hasClass('js-option-selected')) {
                this.noItem.show();
            }
            // this.list.css('margin-bottom', this.marginBottom);
            $(document).find('.totals.redeem').css('margin-bottom', this.marginBottom);
            this.element.addClass('active');
            this.isListOpened = true;
        },
        value: function () {
            return this.selectedOptionContainer.data('selectValue');
        }
    });
    return $.encomage.customSelect;
});
