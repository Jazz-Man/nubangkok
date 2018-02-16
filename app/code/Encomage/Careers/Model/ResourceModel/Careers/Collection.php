<?php
namespace Encomage\Careers\Model\ResourceModel\Careers;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'encomage_careers_collection';
    protected $_eventObject = 'careers_collection';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Encomage\Careers\Model\Careers', 'Encomage\Careers\Model\ResourceModel\Careers');
    }
}