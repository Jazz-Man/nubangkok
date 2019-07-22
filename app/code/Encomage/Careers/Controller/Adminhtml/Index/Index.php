<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;

/**
 * Class Index
 *
 * @package Encomage\Careers\Controller\Adminhtml\Index
 */
class Index extends Action
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var bool|PageFactory
     */
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