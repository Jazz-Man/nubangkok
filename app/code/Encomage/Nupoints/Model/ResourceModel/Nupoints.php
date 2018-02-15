<?php

namespace Encomage\Nupoints\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Nupoints
 * @package Encomage\Nupoints\Model\ResourceModel
 */
class Nupoints extends AbstractDb
{
    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init('encomage_customer_nupoints', 'id');
    }
}