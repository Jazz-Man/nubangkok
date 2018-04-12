<?php
namespace Encomage\Stories\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Stories
 * @package Encomage\Stories\Model\ResourceModel
 */
class Stories extends AbstractDb
{
    /**
     * Class construct.
     */
    protected function _construct()
    {
        $this->_init('encomage_stories', 'entity_id');
    }

    /**
     * @param array $ids
     * @return int
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massDeleteById(array $ids)
    {
        if (!is_array($ids)) {
            throw new \Exception("Invalid param.");
        }
        $where[$this->_idFieldName . ' IN (?)'] = $ids;
        $res = $this->getConnection()->delete($this->getMainTable(), $where);
        return $res;
    }
}