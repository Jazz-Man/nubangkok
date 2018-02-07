define([
        'jquery',
        'Magento_Customer/js/customer-data'
    ], function ($, customerData) {
        return function (config, element) {
            function renderLinks(items) {
                var ul = $(element);
                $.each(items, function (index, item) {
                    ul.append('<li><a href="' + item.href + '">' + item.label + '</a></li>')
                });
            }

            var customer = customerData.get('customer'),
                isLogged = customer() && customer().firstname;
            if (isLogged) {
                if($('.customer-name').hasClass("top-link-myaccount")){
                    $('.customer-name').removeClass("top-link-myaccount")
                }
                $('.customer-name').text("Hi " + customer().firstname);
                renderLinks(config.logged);
            } else {
                if(!$('.customer-name').hasClass("top-link-myaccount")){
                    $('.customer-name').addClass("top-link-myaccount")
                }
                renderLinks(config.notLogged);
            }
        }
    }
);