var config = {
    deps: [
        "js/global"
    ],
    map: {
        '*': {
            niceselect: 'js/plugins/jquery.nice-select',

            sticky: 'js/plugins/jquery.sticky',
            owlcarousel: 'Mageplaza_BetterSlider/js/owl.carousel',
            simpleCropper: 'js/plugins/cropper/jquery.SimpleCropper'
        }
    },
    paths: {
        'owlcarousel': 'Mageplaza_BetterSlider/js/owl.carousel',
        'simpleCropper': 'js/plugins/cropper/jquery.SimpleCropper',
        'Jcrop': 'js/plugins/cropper/jquery.Jcrop'
    },
    shim: {
        'js/plugins/jquery.nice-select': ['jquery'],
        'js/plugins/jquery.sticky': ['jquery'],
        'js/plugins/cropper/jquery.SimpleCropper': ['jquery', 'Jcrop'],
        'js/plugins/cropper/jquery.Jcrop': ['jquery'],
        'Mageplaza_BetterSlider/js/owl.carousel': ['jquery'],
        'Mageplaza_BetterSlider/js/owl.navigation': ['owlcarousel'],
        'Mageplaza_BetterSlider/js/owl.video': ['owlcarousel'],
        'Mageplaza_BetterSlider/js/owl.autoplay': ['owlcarousel']

    }
};