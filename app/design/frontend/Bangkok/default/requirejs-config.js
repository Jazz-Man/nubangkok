var config = {
    deps: [
        "js/global"
    ],
    map: {
        '*': {
            niceselect: 'js/plugins/jquery.nice-select',
            owlcarousel: 'Mageplaza_BetterSlider/js/owl.carousel'
        }
    },
    paths: {
        'owlcarousel': 'Mageplaza_BetterSlider/js/owl.carousel'
    },
    shim: {
        'js/plugins/jquery.nice-select': ['jquery'],
        'Mageplaza_BetterSlider/js/owl.carousel': ['jquery'],
        'Mageplaza_BetterSlider/js/owl.navigation': ['owlcarousel'],
        'Mageplaza_BetterSlider/js/owl.video': ['owlcarousel'],
        'Mageplaza_BetterSlider/js/owl.autoplay': ['owlcarousel']

    }
};