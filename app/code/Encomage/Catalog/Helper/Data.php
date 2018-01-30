<?php

namespace Encomage\Catalog\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getFacebookShareUrl(\Magento\Catalog\Model\Product $product)
    {
        return 'http://www.facebook.com/sharer/sharer.php?u=' . $product->getProductUrl();
    }
}