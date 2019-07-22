<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Magento\Backend\App\Action;
use \Magento\Framework\View\Result\PageFactory;

/**
 * Class Create
 *
 * @package Encomage\Careers\Controller\Adminhtml\Index
 */
class Create extends Action
{
    /**
     * @var bool|PageFactory
     */
    private $resultPageFactory = false;

    /**
     * Form constructor.
     * @param Action\Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Action\Context $context,
        PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Encomage_Careers::careers');
        $resultPage->getConfig()->getTitle()->prepend(__('New Vacancy'));
        return $resultPage;
    }
}