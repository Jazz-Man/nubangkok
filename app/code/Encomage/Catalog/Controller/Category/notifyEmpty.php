<?php

namespace Encomage\Catalog\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Encomage\Catalog\Model\Category\ComingSoonProductFactory;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as Resource;
use Magento\Framework\Controller\ResultFactory;

class notifyEmpty extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var \Encomage\Catalog\Model\Category\ComingSoonProductFactory
     */
    private $_comingSoonProductFactory;

    /**
     * @var \Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct
     */
    private $_resource;

    /**
     * notifyEmpty constructor.
     * @param ComingSoonProductFactory $comingSoonProductFactory
     * @param Resource $resource
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ComingSoonProductFactory $comingSoonProductFactory,
        Resource $resource,
        Context $context,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_resource =$resource;
        $this->_comingSoonProductFactory = $comingSoonProductFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $model= $this->_comingSoonProductFactory->create();
        $model->setData(
            ['category_id' => $params['category_id'], 'email' => $params['notify-email']]);
        $this->_resource->save($model);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('/');
        return $resultRedirect;
    }
}