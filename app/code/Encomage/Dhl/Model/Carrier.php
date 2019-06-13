<?php

namespace Encomage\Dhl\Model;

use Magento\Catalog\Model\Product\Type;

/**
 * Class Carrier
 * @package Encomage\Dhl\Model
 */
class Carrier extends \Magento\Dhl\Model\Carrier
{
    /**
     * @return array
     */
    protected function _getAllItems()
    {
        $allItems = $this->_request->getAllItems();
        $fullItems = [];

        foreach ($allItems as $item) {
            if ($item->getProductType() == Type::TYPE_BUNDLE && $item->getProduct()->getShipmentType()) {
                continue;
            }

            $qty = $item->getQty();
            $changeQty = true;
            $checkWeight = true;
            $decimalItems = [];

            if ($item->getParentItem()) {
                if (!$item->getParentItem()->getProduct()->getShipmentType()) {
                    continue;
                }
                if ($item->getIsQtyDecimal()) {
                    $qty = $item->getParentItem()->getQty();
                } else {
                    $qty = $item->getParentItem()->getQty() * $item->getQty();
                }
            }

            $itemWeight = 0.4;
            if ($item->getIsQtyDecimal() && $item->getProductType() != Type::TYPE_BUNDLE) {
                $productId = $item->getProduct()->getId();
                $stockItemDo = $this->stockRegistry->getStockItem($productId, $item->getStore()->getWebsiteId());
                $isDecimalDivided = $stockItemDo->getIsDecimalDivided();
                if ($isDecimalDivided) {
                    if ($stockItemDo->getEnableQtyIncrements()
                        && $stockItemDo->getQtyIncrements()
                    ) {
                        $itemWeight = $itemWeight * $stockItemDo->getQtyIncrements();
                        $qty = round($item->getWeight() / $itemWeight * $qty);
                        $changeQty = false;
                    } else {
                        $itemWeight = $this->_getWeight($itemWeight * $item->getQty());
                        $maxWeight = $this->_getWeight($this->_maxWeight, true);
                        if ($itemWeight > $maxWeight) {
                            $qtyItem = floor($itemWeight / $maxWeight);
                            $decimalItems[] = ['weight' => $maxWeight, 'qty' => $qtyItem];
                            $weightItem = $this->mathDivision->getExactDivision($itemWeight, $maxWeight);
                            if ($weightItem) {
                                $decimalItems[] = ['weight' => $weightItem, 'qty' => 1];
                            }
                            $checkWeight = false;
                        }
                    }
                } else {
                    $itemWeight = $itemWeight * $item->getQty();
                }
            }

            if ($checkWeight && $this->_getWeight($itemWeight) > $this->_getWeight($this->_maxWeight, true)) {
                return [];
            }

            if ($changeQty
                && !$item->getParentItem()
                && $item->getIsQtyDecimal()
                && $item->getProductType() != Type::TYPE_BUNDLE
            ) {
                $qty = 1;
            }

            if (!empty($decimalItems)) {
                foreach ($decimalItems as $decimalItem) {
                    $fullItems = array_merge(
                        $fullItems,
                        array_fill(0, $decimalItem['qty'] * $qty, $decimalItem['weight'])
                    );
                }
            } else {
                $fullItems = array_merge($fullItems, array_fill(0, $qty, $this->_getWeight($itemWeight)));
            }
        }
        sort($fullItems);

        return $fullItems;
    }
}