<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class OptionsSizes extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Options
{
    /**
     * @return array
     */
    public function getSizes()
    {
        $sizes = [];
        foreach ($this->getConfiguredOptions() as $value) {
            if ($value['label'] == 'Size') {
                $sizes = $value;
                break 1;
            }
        }
        return $sizes;
    }
}