define([
    'jquery',
    'matchMedia'
], function ($, mediaCheck) {
    "use strict";

    return function (config) {

        var $html = $('html'),
            mediaBreakpoint = '(max-width: 768px)',
            desktopContainer = $('.sidebar.sidebar-additional .js-sidebar-container'),
            mobileContainer = $('.navigation .js-sidebar-container'),
            sidebarCategories =  $('.js-sidebar-categories');


        function activateSubMenu(categoryId) {
            var e = $('.main-subcategories ul[data-parent-id="' + categoryId + '"]');
            if (config.currentCategoryId) {
                var active = e.find('li[data-category-id="' + config.currentCategoryId + '"]');
                if (active) {
                    active.addClass('active');
                }
            }
            if (e.hasClass('active')) {
                e.hide();
                e.removeClass('active');
                sidebarCategories.trigger('sub-menu-closed', e);
            } else {
                e.show();
                e.addClass('active');
                sidebarCategories.trigger('sub-menu-show', e);
            }
        }

        // toggle between mobile / desktop
        // ----------------------------------------------------------------
        function menuHtmlToggleContainer(isMobile) {

            if (isMobile === true && !mobileContainer.html()) {
                mobileContainer.html(desktopContainer.html());
            } else if (isMobile === false && !desktopContainer.html()) {
                desktopContainer.html(mobileContainer.html());
            }
        }

        //  category tree
        // ----------------------------------------------------------------
        function init() {
            if (config.activeMainCategoryId) {
                mediaCheck({
                    media: mediaBreakpoint,
                    entry: $.proxy(function () {

                        // mobile
                        // ----------------------------------------------------------------

                        menuHtmlToggleContainer(true)
                    }, this),
                    exit: $.proxy(function () {

                        // desktop
                        // ----------------------------------------------------------------

                        menuHtmlToggleContainer(false)
                    }, this)
                });


                $('.js-category-main[data-category-id="' + config.activeMainCategoryId + '"]').addClass('active');
                if (config.activeCategoryPath) {
                    $.each(config.activeCategoryPath, function (k, v) {
                        activateSubMenu(v)
                    })
                } else {
                    activateSubMenu(config.activeMainCategoryId);
                }

                $(document).on('click', '.js-sidebar-category', function (e) {
                    var el = $(this);
                    if (el.hasClass('js-no-link')) {
                        e.preventDefault();

                    }
                    if (el.hasClass('js-category-main')) {
                        $('.main-categories span').removeClass('active');
                        $('.main-subcategories ul').hide().removeClass('active');
                        el.parent().addClass('active');
                    }
                    activateSubMenu(el.data('categoryId'));
                })
            }
        }

        //  open mobile menu
        // ----------------------------------------------------------------

        $('span[data-action="toggle-nav"]').on('click', function () {
            $html.addClass('nav-before-open nav-open')
        });

        //  close mobile menu
        // ----------------------------------------------------------------

        $('span[data-action="toggle-nav-close"]').on('click', function () {
            $html.removeClass('nav-open');
            setTimeout(function () {
                $html.removeClass('nav-before-open');
            }, 300);
        });

        init();
    };
});
