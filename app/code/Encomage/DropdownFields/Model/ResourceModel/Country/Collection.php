<?php

namespace Encomage\DropdownFields\Model\ResourceModel\Country;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Encomage\DropdownFields\Model\ResourceModel\Country
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';


    protected function _construct()
    {
        $this->_init('Encomage\DropdownFields\Model\Country', 'Encomage\DropdownFields\Model\ResourceModel\Country');
    }

}