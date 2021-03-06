define([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'Magento_Customer/js/customer-data',
    'simpleCropper'
], function ($, modal, alert, customerData) {
    "use strict";

    return function (config, element) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Add new story'),
                buttons: [{
                    text: $.mage.__('Okay, got it'),
                    class: '',
                    click: function () {
                        $('#save-story-form').submit();
                        this.closeModal();
                    }
                },
                    {
                        text: $.mage.__('Close'),
                        class: '',
                        click: function () {
                            this.closeModal();
                        }
                    }]
        },
            popup = modal(options, $('.save-message-modal')),
            cropper = $('.js-cropper');

        $('.js-customer-name').text(customerData.get('customer')().fullname);
        
        cropper.simpleCropper();

        $(".save-button").on('click', function(){
            var image = $('.js-cropper').children('img')[0],
                content = $('#content').val(),
                title = $('#title').val(),
                alertMessage = $.mage.__('Sorry, you forgot the'),
                alertFlag = false;

            if (image) {
                $('.js-attach-file').val(image.currentSrc);
            } 
            
            if (!content) {
                alertMessage = alertMessage
                    + ' '
                    + $.mage.__('content');
                alertFlag = (!alertFlag) ? true : alertFlag;
            }
            
            if (!title) {
                alertMessage = alertMessage
                    + ((!content) ? ',' : '')
                    + ' '
                    + $.mage.__('title');
                alertFlag = (!alertFlag) ? true : alertFlag;
            }
            
            if (!image) {
                alertMessage = alertMessage
                    + ((!title || !content) ? ',' : '')
                    + ' '
                    + $.mage.__('image');
                alertFlag = (!alertFlag) ? true : alertFlag;
            }

            if (alertFlag) {
                alert({
                    content: $.mage.__(alertMessage)
                });
            }
            
            if (content && image && title) {
                $('.save-message-modal').modal("openModal");
            }
        });
        
        $('.js-image-upload-button').on('click', function () {
            cropper.click();
        });
    }
});