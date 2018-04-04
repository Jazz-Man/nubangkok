<?php
namespace Encomage\Stories\Model\ResourceModel\Stories;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Encomage\Stories\Model\ResourceModel\Stories
 */
class Collection extends AbstractCollection
{
    protected $_idFieldName = 'entity_id';
    protected $_eventPrefix = 'encomage_stories_collection';
    protected $_eventObject = 'stories_collection';

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