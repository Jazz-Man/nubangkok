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

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $post = $this->getPost();
        $this->_itemCollection = $post->getRelatedProducts()
            ->addAttributeToSelect('required_options');
        if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setPageSize(
            (int)$this->_scopeConfig->getValue(
                'mfblog/post_view/related_products/number_of_products',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
        $this->_itemCollection->getSelect()->order('rl.position', 'ASC');
        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        return $this;
    }
}