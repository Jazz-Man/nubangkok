<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Magento\Backend\App\Action\Context;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use \Encomage\Careers\Model\CareersFactory as Careers;
use \Encomage\Careers\Model\ResourceModel\Careers as CareersResource;
use Magento\Framework\Exception\LocalizedException;

class Form extends \Magento\Backend\App\Action
{
    protected $_coreRegistry;

    protected $_careersFactory;

    protected $resultPageFactory = false;
    
    protected $careersResource;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Careers $_careersFactory,
        CareersResource $careersResource
    )
    {
        $this->_careersFactory = $_careersFactory;
        $this->careersResource = $careersResource;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        $params = $this->getRequest()->getParam('careers');
        if (is_array($params)) {
            $model = $this->_careersFactory->create();
            $model->setData($params);
            try {
                $this->careersResource->save($model);
            } catch (\Exception $e) {
                throw new LocalizedException(__($e));
            }
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}