<?php

namespace Encomage\ProductNotification\Block;

use Magento\Catalog\Block\Product\View;

/**
 * Class ProductNotification
 * @package Encomage\ProductNotification\Block
 */
class ProductNotification extends View
{
    /**
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('encomage_productNotification/ajax/index');
    }

    /**
     * @return string
     */
    public function getUserEmail()
    {
        return $this->customerSession->isLoggedIn() ? $this->customerSession->getCustomer()->getEmail() : '';
    }

    /**
     * @param int $id
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getProductStockDataById(int $id)
    {
        return $this->stockRegistry->getStockItem($id);
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getProduct()->getId();
    }


    /**
     * @return string
     */
    public function getProductName()
    {
       return $this->getProduct()->getName();
    }

    /**
     * @return array
     */
    public function getProductStockData()
    {
        $items = [];
        if ($this->getProduct()->getTypeId() === 'simple') {
            $items['simple'] =
                  ['product_id'=>$this->getProductId(),
                   'stockData'=>$this->getProductStockDataById($this->getProductId())];
        } else {
            $children = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
            $itemsStockData =[];
            foreach ($children as $item) {
               $stockStatus =$this->stockRegistry->getStockItem((int)$item->getId())->getIsInStock();
               $stockQty = $this->stockRegistry->getStockItem((int)$item->getId())->getQty();
               if(!$stockStatus)
               {
                   $itemsStockData[$item->getId()] = $stockQty;
               }
            }
            if(count($children) === count($itemsStockData)){
                $items['configurable'] = ['product_id'=> $this->getProductId()];
            }
        }
        return $items;
    }
}