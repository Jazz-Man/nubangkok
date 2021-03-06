<?php

namespace Encomage\Catalog\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Helper\Context;
use Magento\CatalogInventory\Api\Data\StockItemInterfaceFactory;
use Encomage\Catalog\Block\Product\ImageBuilder;

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
     * @var ImageBuilder
     */
    protected $imageBuilder;

    /**
     * @var array
     */
    private $productsStockDataCollections = [];

    /**
     * Data constructor.
     *
     * @param Context $context
     * @param StockItemInterfaceFactory $stockItem
     * @param ImageBuilder $imageBuilder
     */
    public function __construct(
        Context $context,
        StockItemInterfaceFactory $stockItem,
        ImageBuilder $imageBuilder
    )
    {
        parent::__construct($context);
        $this->stockItemFactory = $stockItem;
        $this->imageBuilder = $imageBuilder;
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

    /**
     * @return bool
     */
    public function getIsNew()
    {
        return $this->imageBuilder->getIsNew();
    }

    /**
     * @param Product $product
     * @return Product
     */
    public function checkImageForStores(Product $product)
    {
        $image = $product->getImage();
        $smallImage = $product->getSmallImage();
        $thumbnail = $product->getThumbnail();

        if ($image == 'no_selection' && $smallImage == 'no_selection' && $thumbnail == 'no_selection') {
            $mediaGalleryItems = $product->getMediaGalleryImages()->getItems();
            if (count($mediaGalleryItems)) {
                $mediaGalleryItem = array_shift($mediaGalleryItems);
                if ($mediaGalleryItem->getFile()) {
                    $product->setImage($mediaGalleryItem->getFile());
                    $product->setSmallImage($mediaGalleryItem->getFile());
                    $product->setThumbnail($mediaGalleryItem->getFile());
                }

            }
        }

        return $product;
    }
}