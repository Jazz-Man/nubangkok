<?php

namespace Encomage\DropdownFields\Plugin\Checkout\Block;

use \Magento\Checkout\Block\Checkout\LayoutProcessor as Subject;

class LayoutProcessor {

    /**
     * @param Subject $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(Subject $subject, array  $jsLayout) {
        //Shipping Address
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['country_id']['sortOrder'] = 110;
        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
        ['children']['shippingAddress']['children']['shipping-address-fieldset']
        ['children']['postcode']['sortOrder'] = 109;
        //Billing Address on payment method
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children']
        )) {
            $paymentList = $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['payments-list']['children'];
            foreach ($paymentList as $key => $payment) {
                $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
                ['payment']['children']['payments-list']['children'][$key]['children']['form-fields']['children']
                ['country_id']['sortOrder'] = 110;
                $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
                ['children']['shippingAddress']['children']['shipping-address-fieldset']
                ['children']['postcode']['sortOrder'] = 109;
            }
        }
        //Billing Address on payment page
        if (isset($jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']
        )) {
            $jsLayout['components']['checkout']['children']['steps']['children']['billing-step']['children']
            ['payment']['children']['afterMethods']['children']['billing-address-form']['children']['form-fields']
            ['children']['country_id']['sortOrder'] = 110;
            $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']
            ['children']['shippingAddress']['children']['shipping-address-fieldset']
            ['children']['postcode']['sortOrder'] = 109;
        }

        if(isset($jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children']
            ['p2c2ppayment-form'])) {

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children']
            ['p2c2ppayment-form']['children']['form-fields']['children']['region_id']
            ['component'] = 'Eadesigndev_RomCity/js/form/element/region';

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children']
            ['p2c2ppayment-form']['children']['form-fields']['children']['country_id']['sortOrder'] = 107;

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children']
            ['p2c2ppayment-form']['children']['form-fields']['children']['region_id']['sortOrder'] = 108;

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children']
            ['p2c2ppayment-form']['children']['form-fields']['children']['city_id']['sortOrder'] = 109;

            $jsLayout['components']['checkout']['children']['steps']['children']
            ['billing-step']['children']['payment']['children']['payments-list']['children']
            ['p2c2ppayment-form']['children']['form-fields']['children']['city']['visible'] = false;
        }

        return $jsLayout;
    }
}