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
                $('.js-logged-customer .customer-name').text("Hi " + customer().firstname);
                renderLinks(config.logged);
            } else {
                renderLinks(config.notLogged);
            }
        }
    }
);