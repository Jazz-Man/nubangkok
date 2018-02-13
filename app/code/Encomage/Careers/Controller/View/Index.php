<?php
namespace Encomage\Careers\Controller\View;

use \Magento\Framework\App\Action\Context;
use \Magento\Framework\View\Result\PageFactory;
use \Encomage\Careers\Model\CareersFactory;
use \Encomage\Careers\Model\ResourceModel\Careers;
use \Magento\Framework\Registry;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Encomage\Careers\Model\ResourceModel\Careers
     */
    private $careersResource;
    /**
     * @var \Encomage\Careers\Model\CareersFactory
     */
    private $careersFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Index constructor.
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param CareersFactory $careersFactory
     * @param Careers $careersResource
     * @param Registry $registry
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        CareersFactory $careersFactory,
        Careers $careersResource,
        Registry $registry
    )
    {
        $this->_pageFactory = $pageFactory;
        $this->careersResource = $careersResource;
        $this->careersFactory = $careersFactory;
        $this->registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        if (!$id) {
            $this->_redirect('careers/listing');
            return;
        }
        $this->_initModel();
        return $this->_pageFactory->create();
    }

    /**
     * @return $this
     */
    protected function _initModel()
    {
        $id = (int)$this->getRequest()->getParam('id', null);
        if ($id) {
            $careersModel = $this->careersFactory->create();
            $this->careersResource->load($careersModel, $id);
            $this->registry->register('current_career', $careersModel);
        }
        return $this;
    }
}