<?php

namespace Encomage\Nupoints\Model\ResourceModel\Nupoints;

use Encomage\Nupoints\Model\ResourceModel\Nupoints;
use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Encomage\Nupoints\Model\ResourceModel\Redeem
 */
class Collection extends AbstractCollection
{
    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init(
            \Encomage\Nupoints\Model\Nupoints::class,
            Nupoints::class
        );
    }
}