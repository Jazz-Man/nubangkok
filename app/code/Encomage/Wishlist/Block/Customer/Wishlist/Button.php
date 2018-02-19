<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist;

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