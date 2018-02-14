<?php

namespace Encomage\Catalog\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public function getNotifyMeEmptyCategoryUrl(\Magento\Catalog\Model\Category $category)
    {
        return $this->_getUrl('bangkok_catalog/category/notifyEmpty', ['category_id' => (int)$category->getId()]);
    }

    public function getFacebookSharedLink(\Magento\Catalog\Model\Product $product)
    {
        return 'http://www.facebook.com/sharer/sharer.php?u=' . $product->getProductUrl();
    }
}