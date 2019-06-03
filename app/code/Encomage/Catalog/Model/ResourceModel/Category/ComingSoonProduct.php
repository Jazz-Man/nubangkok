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
     * @param array $categoryIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteEmailsByCategoryIds(array $categoryIds)
    {
        $where = ['category_id IN ( ? )' => $categoryIds];
        $this->getConnection()->delete($this->getTable($this->getMainTable()), $where);
        return $this;
    }
}