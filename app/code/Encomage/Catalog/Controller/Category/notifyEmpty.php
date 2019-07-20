<?php

namespace Encomage\Catalog\Controller\Category;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Encomage\Catalog\Model\Category\ComingSoonProductFactory;
use Encomage\Catalog\Model\ResourceModel\Category\ComingSoonProduct as Resource;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class notifyEmpty
 *
 * @package Encomage\Catalog\Controller\Category
 */
class notifyEmpty extends Action
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

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $model= $this->_comingSoonProductFactory->create();
        $model->setData(
            ['category_id' => $params['category_id'], 'email' => $params['notify-email']]);
        $this->_resource->save($model);
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}