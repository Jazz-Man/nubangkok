define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    "use strict";

    return function (config, element) {
        element = $(element);
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'popup modal title',
            buttons: [{
                text: $.mage.__('Continue'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#popup-modal'));

        $('#popup-modal').modal('openModal');


    };
});