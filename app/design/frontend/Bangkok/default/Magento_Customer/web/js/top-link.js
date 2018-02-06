define(
    ['jquery', 'Magento_Customer/js/customer-data'],
    function ($, customerData) {
        return function () {
            var customer = customerData.get('customer'),
                isLogged = customer() && customer().firstname;
            if (isLogged) {
                $('.js-logged-customer').show();
                $('.js-logged-customer .customer-name').text("Hi " + isLogged);
            } else {
                $('.js-not-logged-customer').show();
            }
        }
    }
);