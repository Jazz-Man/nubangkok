var config = {
    deps: [
        "js/global"
    ],
    map: {
        '*': {
            niceselect: 'js/plugins/jquery.nice-select',
            customSelect: 'js/plugins/custom-select',
            sticky: 'js/plugins/jquery.sticky',
            owlcarousel: 'Mageplaza_BannerSlider/js/owl.carousel',
            simpleCropper: 'js/plugins/cropper/jquery.SimpleCropper'
        }
    },
    paths: {
        'owlcarousel': 'Mageplaza_BannerSlider/js/owl.carousel',
        'simpleCropper': 'js/plugins/cropper/jquery.SimpleCropper',
        'Jcrop': 'js/plugins/cropper/jquery.Jcrop'
    },
    shim: {
        'js/plugins/jquery.nice-select': ['jquery'],
        'js/plugins/jquery.sticky': ['jquery'],
        'js/plugins/cropper/jquery.SimpleCropper': ['jquery', 'Jcrop'],
        'js/plugins/cropper/jquery.Jcrop': ['jquery'],
        'Mageplaza_BannerSlider/js/owl.carousel': ['jquery'],
        'Mageplaza_BannerSlider/js/owl.navigation': ['owlcarousel'],
        'Mageplaza_BannerSlider/js/owl.video': ['owlcarousel'],
        'Mageplaza_BannerSlider/js/owl.autoplay': ['owlcarousel']

    }
};