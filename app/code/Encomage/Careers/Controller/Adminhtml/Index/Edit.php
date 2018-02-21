<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

class Edit extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $this->_forward('form');
    }
}