<?php

namespace Encomage\Catalog\Block\Product\View;

class Shared extends \Magento\Catalog\Block\Product\View
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Catalog::product/view/shared.phtml');
    }

    public function getFacebookSharedLink()
    {
        return 'http://www.facebook.com/sharer/sharer.php?u=' . $this->getProduct()->getProductUrl();
    }
}