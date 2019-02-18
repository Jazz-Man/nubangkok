<?php

namespace Encomage\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\CatalogInventory\Api\Data\StockItemInterface;

/**
 * Class Data
 * @package Encomage\Catalog\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @var Item
     */
    protected $stockItem;

    /**
     * Data constructor.
     * @param Context $context
     * @param StockItemInterface $stockItem
     */
    public function __construct(
        Context $context,
        StockItemInterface $stockItem
    )
    {
        parent::__construct($context);
        $this->stockItem = $stockItem;
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
     * @param int $productId
     * @return bool
     */
    public function getProductStockStatus(int $productId)
    {
        $stockData = $this->stockItem->load($productId, 'product_id');
        if (!$stockData->getId()){
            return false;
        }

        if ($stockData->getQty() && $stockData->getIsInStock()) {
            return true;
        }

        return false;
    }

    /**
     * @param array $productChildren
     * @return bool
     */
    public function checkProductStockStatusForProductNotify(array $productChildren)
    {
        $productNotify = [];
        foreach ($productChildren as $productChild) {
            $productStockData  = $this->stockItem->load($productChild->getId(), 'product_id');

            if (!$productStockData->getIsInStock()) {
                $productNotify[$productChild->getId()] = true;
            }
        }
        if (count($productChildren) === count($productNotify)) {
            return true;
        } else {
            return false;
        }
    }
}