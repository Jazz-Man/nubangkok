<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class Shared extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column {


    /**
     * @return string
     */
    public function getFacebookSharedLink()
    {
        return 'http://www.facebook.com/sharer/sharer.php?u=' . $this->getProductItem()->getProductUrl();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductItem()
    {
        return $this->getItem()->getProduct();
    }
}