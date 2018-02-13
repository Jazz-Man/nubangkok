<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class OptionsColor extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Options{

    /**
     * @return array
     */
    public function getColors()
    {
        $colors = [];
        foreach ($this->getConfiguredOptions() as $value) {
            if ($value['label'] == 'Color') {
                $colors = $value;
                break 1;
            }
        }
        return $colors;
    }
}