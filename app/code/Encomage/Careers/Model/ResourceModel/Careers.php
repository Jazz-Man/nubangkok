<?php
namespace Encomage\Careers\Model\ResourceModel;

class Careers extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected function _construct()
    {
        $this->_init('encomage_careers', 'id');
    }
}