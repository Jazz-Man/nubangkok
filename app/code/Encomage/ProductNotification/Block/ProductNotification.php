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
            $customStockData = $this->getOutStockInfo();
            if($customStockData['childrenCount'] === count($customStockData['outOfStockItems'])){
                $items['configurable'] = ['product_id'=> $this->getProductId()];
            }
        }
        return $items;
    }

    /**
     * @return array
     */
    public function getOutStockInfo()
    {
        $outOfStockItems = [];
        $children = $this->getProduct()->getTypeInstance()->getUsedProducts($this->getProduct());
        foreach ($children as $item) {
            $stockStatus =$this->stockRegistry->getStockItem((int)$item->getId())->getIsInStock();
            $stockQty = $this->stockRegistry->getStockItem((int)$item->getId())->getQty();
            if(!$stockStatus)
            {
                $outOfStockItems[$item->getId()]['qty'] =$stockQty;
                foreach ($this->getAttributes() as $attributeCode){
                    $outOfStockItems[$item->getId()]['options'][$attributeCode] = $item->getData($attributeCode);
                }
            }
        }
        return ['childrenCount' => count($children), 'outOfStockItems' => $outOfStockItems];
    }

    /**
     * @return array|null
     */
    public function getAttributes()
    {
        $configurableProductOptions = $this->getProduct()->getExtensionAttributes()->getConfigurableProductOptions();
        $attributes = [];
        if(count($configurableProductOptions) > 0) {
            foreach ($configurableProductOptions as $option) {
                $attributes[] = $option->getProductAttribute()->getAttributeCode();
            }
        }
        return $attributes;
    }

    /**
     * @param $array
     * @return string
     */
    public function customSerializer($array)
    {
        return $this->_jsonEncoder->encode($array);
    }
}