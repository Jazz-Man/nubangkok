<?php

namespace Encomage\ProductNotification\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Encomage\ProductNotification\Setup\InstallSchema;

/**
 * Class ProductNotification
 * @package Encomage\ProductNotification\Model\ResourceModel
 */
class ProductNotification extends AbstractDb
{
    /**
     * ProductNotification constructor.
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * class construct
     */
    protected function _construct()
    {
        $this->_init(InstallSchema::TABLE_NAME, 'notification_id');
    }

    /**
     * @param array $ids
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteRecordsByIds(array $ids)
    {
        $this->getConnection()->delete($this->getMainTable(), ['notification_id IN ( ? )' => $ids]);
    }
}