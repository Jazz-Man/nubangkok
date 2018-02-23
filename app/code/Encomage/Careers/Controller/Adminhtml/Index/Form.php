<?php

namespace Encomage\Careers\Controller\Adminhtml\Index;

use \Magento\Backend\App\Action;
use \Magento\Framework\Registry;
use \Magento\Framework\View\Result\PageFactory;
use Encomage\Careers\Model;
use Magento\Framework\Exception\LocalizedException;
use Magento\PageCache\Model\Config;
use Magento\Framework\App\Cache\TypeListInterface;

class Form extends Action
{
    protected $config;
    protected $typeList;
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

    protected $_cacheState;
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
        Config $config,
        TypeListInterface $typeList,
        \Magento\Framework\App\Cache\StateInterface $cacheState,
        Model\ResourceModel\Careers $careersResource,
        Model\CareersFactory $careersFactory
    )
    {
        $this->_cacheState = $cacheState;
        $this->config = $config;
        $this->typeList = $typeList;
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
                if ($this->_cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)) {
                    $this->typeList->invalidate(['full_page', 'block_html']);
                }
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