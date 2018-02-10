<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;

    protected $resultPageFactory = false;

    /**
     * Index constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(Context $context, Registry $coreRegistry, PageFactory $resultPageFactory)
    {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Encomage_Careers::careers');
        $resultPage->getConfig()->getTitle()->prepend((__('Careers')));

        return $resultPage;
    }
}