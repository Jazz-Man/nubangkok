<?php
namespace Encomage\Careers\Model\ResourceModel\Careers;

use Encomage\Careers\Model\Careers as CareersModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Encomage\Careers\Model\ResourceModel\Careers as CareersResource;

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
        $this->_init(CareersModel::class, CareersResource::class);
    }
}