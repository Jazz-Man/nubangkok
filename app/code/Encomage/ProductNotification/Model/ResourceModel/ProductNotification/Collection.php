<?php

namespace Encomage\ProductNotification\Model\ResourceModel\ProductNotification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Encomage\ProductNotification\Model\ProductNotification as Model;
use Encomage\ProductNotification\Model\ResourceModel\ProductNotification as ResourceModel;

/**
 * Class Collection
 * @package Encomage\ProductNotification\Model\ResourceModel\ProductNotification
 */
class Collection extends AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }

}