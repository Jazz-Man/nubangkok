<?php

namespace Encomage\Catalog\Model\ResourceModel\Category\ComingSoon;

use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as ComingSoonProductAlias;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Encomage\Catalog\Model\Category\ComingSoonProduct;

/**
 * Class Collection
 *
 * @package Encomage\Catalog\Model\ResourceModel\Category\ComingSoon
 */
class Collection extends AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            ComingSoonProduct::class, ComingSoonProductAlias::class
        );

    }
}