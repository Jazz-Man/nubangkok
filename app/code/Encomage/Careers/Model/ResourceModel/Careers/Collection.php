<?php
namespace Encomage\Careers\Model\ResourceModel\Careers;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Encomage\Careers\Model\ResourceModel\Careers;

/**
 * Class Collection
 *
 * @package Encomage\Careers\Model\ResourceModel\Careers
 */
class Collection extends AbstractCollection
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
        $this->_init(Careers::class, Careers::class);
    }
}