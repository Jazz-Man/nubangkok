define([
    'jquery',
    'Magento_Ui/js/modal/modal'
], function ($, modal) {
    "use strict";

    return function (config, element) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Add new story'),
            buttons: [{
                text: $.mage.__('Continue'),
                class: '',
                click: function () {
                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#form-modal'));
        $("#add-form-modal").on('click',function(){
            $('#form-modal').modal("openModal");
        });
    }
});