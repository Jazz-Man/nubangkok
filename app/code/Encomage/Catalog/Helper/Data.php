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
     * @var StockItemInterfaceFactory
     */
    protected $stockItemInterfaceFactory;

    /**
     * Data constructor.
     * @param Context $context
     * @param StockItemInterfaceFactory $stockItemInterfaceFactory
     */
    public function __construct(
        Context $context,
        StockItemInterfaceFactory $stockItemInterfaceFactory
    )
    {
        parent::__construct($context);
        $this->stockItemInterfaceFactory = $stockItemInterfaceFactory;
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
        $stockData = $this->stockItemInterfaceFactory->create();
        $stockData->load($productId);
        if (!$stockData->getId()){
            return false;
        }

        if ($stockData->getQty() && $stockData->getIsInStock()) {
            return true;
        }

        return false;
    }
}