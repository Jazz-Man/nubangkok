<?php

namespace Encomage\ProductNotification\Model;

use Magento\Framework\Model\AbstractModel;
use Encomage\ProductNotification\Model\ResourceModel\ProductNotification as ResourceModel;

/**
 * Class ProductNotification
 * @package Encomage\ProductNotification\Model
 */
class ProductNotification extends AbstractModel
{
    /**
     * class construct
     */
    protected function _construct()
    {
        $this->_init(ResourceModel::class);
    }

}