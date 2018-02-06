<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use \Magento\Framework\ObjectManagerInterface as ObjectManager;
use \Encomage\Careers\Model\CareersFactory as Careers;

class Save extends \Magento\Backend\App\Action
{
    protected $_objectManager;


    public function __construct(Context $context, Registry $coreRegistry, PageFactory $resultPageFactory, ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $post = $this->getRequest()->getPostValue();
        $model = $this->_objectManager->create(Careers::class);
        $model->setData('title', $post['title']);
        $model->setData('recipient_email', $post['recipient_email']);
        $model->save();
    }
}