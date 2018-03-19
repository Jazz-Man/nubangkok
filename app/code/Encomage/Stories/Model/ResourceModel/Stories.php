<?php

namespace Encomage\Stories\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Stories extends AbstractDb
{
    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init('encomage_stories', 'entity_id');
    }
}