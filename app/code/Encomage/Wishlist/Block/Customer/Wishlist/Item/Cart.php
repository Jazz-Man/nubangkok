<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

use Magento\CatalogInventory\Api\StockRegistryInterface;

class Cart extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Column\Cart
{

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        StockRegistryInterface $stockRegistry,
        array $data = [])
    {
        parent::__construct($context, $httpContext, $data);
        $this->_stockRegistry = $stockRegistry;
    }

    /**
     * @return string
     */
    public function getProductSku()
    {
        return $this->getItem()->getProduct()->getSku();
    }

    /**
     * @return int
     */
    public function getItemStockStatus()
    {
        return $this->_stockRegistry->getStockItemBySku($this->getProductSku());
    }
}