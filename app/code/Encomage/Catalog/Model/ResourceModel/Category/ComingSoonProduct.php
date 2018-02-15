<?php

namespace Encomage\Catalog\Model\ResourceModel\Category;

class ComingSoonProduct extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define main table
     */
    protected function _construct()
    {
        $this->_init('coming_soon_category_emails', 'id');
    }

    /**
     * @param $categoryId
     * @return $this
     */
    public function deleteEmailsByCategoryId($categoryId)
    {
        $where = ['category_id = ?' => $categoryId];
        $this->getConnection()->delete($this->getTable($this->getMainTable()), $where);
        return $this;
    }
}