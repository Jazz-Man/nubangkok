define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'simpleCropper'
], function ($, modal) {
    "use strict";

    return function (config, element) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Add new story'),
            buttons: [{
                text: $.mage.__('Close'),
                class: '',
                click: function () {
                    // $('#save-story-form').submit();
                    this.closeModal();
                }
            }]
        };
$('.js-cropper').simpleCropper();
        $('.js-attach-file').on('change', function () {
            $('.js-attach-file');
        })
        var popup = modal(options, $('.save-message-modal'));
        $(".save-button").on('click',function(){
            if ($('#content').val()) {
                $('.save-message-modal').modal("openModal");
            }
        });
    }
});