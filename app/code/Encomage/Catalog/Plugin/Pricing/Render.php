<?php

namespace Encomage\Catalog\Plugin\Pricing;
/**
 * Class Render
 *
 * @package Encomage\Catalog\Plugin\Pricing
 */
class Render
{
    /**
     * @param $subject
     * @param $html
     * @param $priceType
     * @return string
     */
    public function afterRender($subject, $html, $priceType)
    {
        return (empty($html) && $priceType == 'final_price') 
            ? "<div class=\"price-box\"><span class=\"product-out-of-stock\">" . __('Out of stock') . "</span></div>" 
            : $html;
    }
}