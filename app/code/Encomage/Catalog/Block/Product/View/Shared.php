<?php
namespace Encomage\Catalog\Block\Product\View;

class Shared extends \Magento\Catalog\Block\Product\View
{
    public function getFacebookSharedLink()
    {
        return 'http://www.facebook.com/sharer/sharer.php?u=' . $this->getProduct()->getProductUrl();
    }
}