<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class Button extends \Magento\Wishlist\Block\Customer\Wishlist\Button
{

    /**
     * @return int
     */
    public function getItemCount()
    {
        return (int)$this->_wishlistData->getItemCount();
    }
}