<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Magento\Backend\App\Action;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use Encomage\Careers\Model;
use Magento\Framework\Exception\LocalizedException;

class Form extends Action
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var Model\CareersFactory
     */
    protected $_careersFactory;
    /**
     * @var bool|PageFactory
     */
    protected $_resultPageFactory = false;
    /**
     * @var Model\ResourceModel\Careers
     */
    protected $_careersResource;

    /**
     * Form constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Model\ResourceModel\Careers $careersResource
     * @param Model\CareersFactory $careersFactory
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Model\ResourceModel\Careers $careersResource,
        Model\CareersFactory $careersFactory
    )
    {
        $this->_careersFactory = $careersFactory;
        $this->_careersResource = $careersResource;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * @return $this
     * @throws LocalizedException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParam('careers');
        if (is_array($params)) {
            $model = $this->_careersFactory->create();
            $model->setData($params);
            try {
                $this->_careersResource->save($model);
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