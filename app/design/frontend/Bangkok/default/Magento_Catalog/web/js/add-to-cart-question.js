define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    "use strict";

    return function (config, element) {
        element = $(element);
        var addToCartBtn = $('#product-addtocart-button'),
            modalWindwow = $('.js-message');
        addToCartBtn.attr('disabled', true);

        function isShowQuestion() {
            if (Boolean(parseInt($('select.swatch-select.size option:selected').val()))
                && Boolean(parseInt($('#qty').val()))) {
                modalWindwow.show();
            }
        }

        $(document).on('change', 'select.swatch-select.size', function () {
            isShowQuestion();
        });


        $(document).on('click', '.swatch-attribute.color .swatch-option.color', function (e) {
            isShowQuestion();
        });

        $('.js-answer').on('click', function () {
            if ($(this).hasClass('js-show-model')) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: [
                        {
                            text: $.mage.__('Open Live Chat'),
                            class: 'product-question-modal-btn-consultant',
                            click: function () {
                                //TODO: open chant
                                //addToCartBtn.attr('disabled', false);
                                this.closeModal();
                            }
                        },
                        {
                            text: $.mage.__('Add to bag now'),
                            class: 'product-question-modal-btn-add-to-cart',
                            click: function () {
                                $('#product_addtocart_form').submit();
                                this.closeModal();
                            }
                        }
                    ]
                };
                var popup = modal(options, element);
                element.modal('openModal');
            } else {
                addToCartBtn.attr('disabled', false);
            }
        });


    };
});