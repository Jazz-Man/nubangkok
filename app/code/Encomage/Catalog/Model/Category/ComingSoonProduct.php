<?php

namespace Encomage\Catalog\Model\Category;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as ComingSoonProductResource;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoon\Collection as ComingSoonProductResourceCollection;
class ComingSoonProduct  extends AbstractModel
{
    public function __construct(
        Context $context,
        Registry $registry,
        ComingSoonProductResource $resource,
        ComingSoonProductResourceCollection $resourceCollection,
        array $data = [])
    {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data);
    }

    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct');
    }
}
