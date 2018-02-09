<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;


class Stock extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column
{
    protected $_stockRegistry;

    /**
     * Stock constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        array $data = []
    )
    {
        parent::__construct($context, $httpContext, $data);
        $this->_stockRegistry = $stockRegistry;
    }

    /**
     * @return int
     */
    public function getProductSku()
    {
        return $this->getItem()->getProduct()->getSku();
    }

    /**
     * @return float
     */
    public function getStockQty()
    {
        $sku = $this->getProductSku();
        return round($this->_stockRegistry->getStockStatusBySku($sku)->getQty());
    }

}