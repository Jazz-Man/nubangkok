<?php
namespace Encomage\Blog\Block\Post\Product;
/**
 * Class Items
 * @package Encomage\Blog\Block\Post\Product
 */
class Items extends \Magefan\Blog\Block\Post\View\RelatedProducts
{
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getRelatedProducts()
    {
        $_collection = $this->_prepareCollection()->getItems();
        return $_collection;
    }
}