<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

class Cart extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart
{

    protected $_stockRegistry;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = [])
    {
        parent::__construct($context, $httpContext, $data);
        $this->_stockRegistry = $stockRegistry;
    }

    public function getProductSku()
    {
        return $this->getItem()->getProduct()->getSku();
    }

    /**
     * @return int
     */
    public function getItemStockStatus()
    {
        $sku = $this->getProductSku();
        return (int)$this->_stockRegistry->getStockStatusBySku($sku)->getStockStatus();
    }
}