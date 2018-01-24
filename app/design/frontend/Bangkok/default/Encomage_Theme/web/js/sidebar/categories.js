define([
    'jquery'
], function ($) {
    "use strict";

    return function (config, element) {

        function activateSubMenu(categoryId) {
            var e = $('.main-subcategories ul[data-parent-id="' + categoryId + '"]');
            if (config.currentCategoryId) {
                var active = e.find('li[data-category-id="' + config.currentCategoryId + '"]');
                if (active) {
                    active.addClass('active');
                }
            }
            e.show();
        }

        if (config.activeMainCategoryId) {
            $('.js-category-main[data-category-id="' + config.activeMainCategoryId + '"]').addClass('active');
            if (config.activeCategoryPath) {
                $.each(config.activeCategoryPath, function (k, v) {
                    activateSubMenu(v)
                })
            } else {
                activateSubMenu(config.activeMainCategoryId);
            }

            $('.js-sidebar-category').on('click', function (e) {
                var el = $(this);
                if (el.hasClass('js-no-link')) {
                    e.preventDefault();

                }
                if (el.hasClass('js-category-main')) {
                    $('.main-categories span').removeClass('active');
                    $('.main-subcategories ul').hide();
                    el.parent().addClass('active');
                }
                activateSubMenu(el.data('categoryId'));
            })
        }
    };
});