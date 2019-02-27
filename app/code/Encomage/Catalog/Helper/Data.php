<?php

namespace Encomage\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;

/**
 * Class Data
 * @package Encomage\Catalog\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var Item
     */
    protected $stockItemFactory;

    /**
     * @var array
     */
    private $productsStockDataCollections = [];

    /**
     * Data constructor.
     * @param Context $context
     * @param StockItemInterface $stockItem
     */
    public function __construct(
        Context $context,
        StockItemInterfaceFactory $stockItem
    )
    {
        parent::__construct($context);
        $this->stockItemFactory = $stockItem;
    }

    /**
     * @param Category $category
     * @return string
     */
    public function getNotifyMeEmptyCategoryUrl(Category $category)
    {
        return $this->_getUrl('bangkok_catalog/category/notifyEmpty', ['category_id' => (int)$category->getId()]);
    }

    /**
     * @param Product $product
     * @return string
     */
    public function getFacebookSharedLink(Product $product)
    {
        return 'http://www.facebook.com/sharer/sharer.php?u=' . $product->getProductUrl();
    }

    /**
     * @param array $productChildren
     * @param int $productId
     * @return bool
     */
    public function checkProductStockStatus(array $productChildren, int $productId)
    {
        $productStockDataCollection = [];
        $productNotify = [];
        foreach ($productChildren as $productChild) {
            $productStockData = $this->stockItemFactory->create()->load($productChild->getId(), 'product_id');
            if (!$productStockData->getId()) {
                return false;
            }

            if (!$productStockData->getIsInStock() || $productStockData->getQty() === 0) {
                $productNotify[$productChild->getId()] = true;
            }
            $productStockDataCollection[$productChild->getId()] = $productStockData;
        }

        if (count($productChildren) === count($productNotify)) {
            return true;
        } elseif (isset($productStockDataCollection[$productId])) {
            $currentChildProduct = $productStockDataCollection[$productId];
            if ($currentChildProduct->getIsInStock() && $currentChildProduct->getQty()) {
                return true;
            }
        }

        return false;
    }
}