define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    "use strict";

    return function (config, element) {
        element = $(element);
        var addToCartBtn = $('#product-addtocart-button');
        addToCartBtn.attr('disabled', true);
        $('.js-answer').on('click', function () {
            if ($(this).hasClass('js-show-model')) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: $.mage.__('popup modal title'),
                    buttons: [{
                        text: $.mage.__('Continue'),
                        class: 'product-question-modal-btn',
                        click: function () {
                            addToCartBtn.attr('disabled', false);
                            this.closeModal();
                        }
                    }]
                };
                var popup = modal(options, element);
                element.modal('openModal');
            } else {
                addToCartBtn.attr('disabled', false);
            }
        });


    };
});