var config = {
    deps: [
        "js/global"
    ],
    map: {
        '*': {
            niceselect: 'js/plugins/jquery.nice-select'
        }
    },
    shim: {
        'js/plugins/jquery.nice-select': ['jquery']
    }
};