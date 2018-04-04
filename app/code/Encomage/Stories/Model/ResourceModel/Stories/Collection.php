<?php

namespace Encomage\Stories\Model\ResourceModel\Stories;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Encomage\Stories\Model\ResourceModel\Stories
 */
class Collection extends AbstractCollection
{
    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init(
            \Encomage\Stories\Model\Stories::class,
            \Encomage\Stories\Model\ResourceModel\Stories::class
        );
    }
}