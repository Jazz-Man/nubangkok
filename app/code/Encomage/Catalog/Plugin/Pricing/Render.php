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
        $html2 = "<div class=\"price-box\"><span class=\"product-out-of-stock\">" . __('Out of stock') . "</span></div>";

        return (empty($html) && $priceType == 'final_price') ? $html2 : $html;
    }
}