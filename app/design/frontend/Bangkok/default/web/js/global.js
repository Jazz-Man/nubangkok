define([
    'jquery',
    'matchMedia',
    'domReady!',
    'niceselect',
    'sticky'
], function ($, mediaCheck) {
    "use strict";

    //variables
    //---------------------------------------------
    var $pageHeader = $('header.page-header'), $sidebarAdditionalEl = $('.sidebar.sidebar-additional'),
        $mediaBreakpoint = '(max-width: 768px)';
    
    //init selections
    //---------------------------------------------
    $('select').niceSelect();

    $sidebarAdditionalEl.css('min-height', $('.columns').outerHeight());

    $('.js-sidebar-categories').on('sub-menu-show', function (e) {
        $sidebarAdditionalEl.css('min-height', $('.left-sidebar-container.js-sticky-sidebar').outerHeight());
    });

    //sticky elements
    //---------------------------------------------
    setTimeout(function(){
        $('.js-sticky-sidebar').sticky({
            topSpacing: $pageHeader.outerHeight() + 5,
            bottomSpacing: $('.page-footer').outerHeight()
        });
        $pageHeader.sticky({zIndex: 9999});
    }, 100);



    //Use for change image on click
    //included on where-to-buy CMS page
    //---------------------------------------------
    $(document).on('click', '.js-revert-image-on-click', function () {
        var target = $($(this).data('target'));
        if (target) {
            target.attr('src', $(this).data('src'));
            $('.js-revert-image-on-click').removeClass('active');
            $(this).addClass('active');
        }
    });

    //Accordion
    //---------------------------------------------
    $(document).on('click', '.js-accordion', function () {
        var e = $(this),
            target = e.next();
        e.toggleClass('active');
        if (parseInt(target.css('max-height'))) {
            target.css('max-height', 0);
        } else {
            target.css('max-height', target.prop('scrollHeight') + 'px');
        }
    });


    /* start: trash with images */

    /*function mobileImageCrop() {
        var e = $('.image-mobile-crop');
        if (e.length) {
            e.show();
        } else {
            var img = $('.cms-index-index.cms-home #maincontent .columns p img, .cms-coming-soon-category p img');
            $.each(img, function (index, item) {
                item = $(item);
                var $src = item.attr('src');
                $("<img/>", {
                    load: function () {
                        item.hide();
                        var h = this.height, px = -320;
                        if (!$('body').hasClass('cms-home')) {
                            px = 0;
                        } else {
                            $('#maincontent').css({padding: 0})
                        }
                        item.after($('<div class="image-mobile-crop" ' +
                            'style="' +
                            'background-image: url(' + $src + ');' +
                            'width: 100%;' +
                            'min-height: ' + h + 'px;' +
                            'background-position-x: ' + px + 'px;' +
                            'background-size: 1000px;' +
                            'background-repeat: no-repeat;"></div>'));
                    },
                    src: $src
                });

            });
        }
    }

    function desktopImageCrop() {
        $('.cms-index-index.cms-home #maincontent .columns img').show();
        $('#maincontent').css({'padding-right': '20px', 'padding-left': '20px'});
        $('.image-mobile-crop').hide();
    }*/

    /* end: trash with images */

    /* Responsive  */

    mediaCheck({
        media: $mediaBreakpoint,
        entry: $.proxy(function () {

            // mobile
            // ----------------------------------------------------------------

            //mobileImageCrop();
        }, this),
        exit: $.proxy(function () {

            // desktop
            // ----------------------------------------------------------------
            //desktopImageCrop();
        }, this)
    });
});
