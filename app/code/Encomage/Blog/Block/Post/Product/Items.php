<?php
namespace Encomage\Blog\Block\Post\Product;
use Magefan\Blog\Block\Post\View\RelatedProducts;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Items
 *
 * @package Encomage\Blog\Block\Post\Product
 */
class Items extends RelatedProducts
{
    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getRelatedProducts()
    {
        return $this->_prepareCollection()->getItems();
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
                ScopeInterface::SCOPE_STORE
            )
        );
        $this->_itemCollection->getSelect()->order('rl.position', 'ASC');
        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }
        return $this;
    }
}