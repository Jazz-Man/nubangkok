<?php
namespace Encomage\Careers\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Careers
 *
 * @package Encomage\Careers\Model\ResourceModel
 */
class Careers extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('encomage_careers', 'id');
    }

    /**
     * @param array $ids
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massDeleteById(array $ids)
    {
        if (!is_array($ids)) {
            throw new \Exception("Invalid param.");
        }
        $where['id IN (?)'] = $ids;
        $this->getConnection()->delete($this->getMainTable(), $where);
    }
}