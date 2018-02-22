<?php

namespace Encomage\Catalog\Model\ResourceModel\Category\ComingSoon;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            'Encomage\Catalog\Model\Category\ComingSoonProduct',
            'Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct'
        );

    }
}