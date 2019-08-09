<?php

namespace UpMedio\PricingRender\Plugin\Magento\Framework\Pricing;

use Magento\Framework\Pricing\Render as RenderAlias;

/**
 * Class Render.
 */
class Render
{

    /**
     * @param RenderAlias $subject
     * @param string      $result
     * @param mixed       $priceType
     *
     * @return string|null
     */
    public function afterRender(RenderAlias $subject, $result, $priceType): ?string
    {
        return (empty($result) && 'final_price' === $priceType) ? '<div class="price-box"><span class="product-out-of-stock">' . __('Out of stock') . '</span></div>' : $result;
    }
}
