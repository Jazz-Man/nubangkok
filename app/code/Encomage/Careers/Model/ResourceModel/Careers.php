<?php
namespace Encomage\Careers\Model\ResourceModel;

use Magento\Framework\Validator\Exception;

class Careers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
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