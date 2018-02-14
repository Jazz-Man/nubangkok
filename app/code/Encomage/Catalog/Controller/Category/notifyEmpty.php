<?php

namespace Encomage\Catalog\Controller\Category;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Encomage\Catalog\Model\Category\ComingSoonProductFactory;
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
     * notifyEmpty constructor.
     * @param ComingSoonProductFactory $comingSoonProductFactory
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ComingSoonProductFactory $comingSoonProductFactory,
        Context $context,
        ScopeConfigInterface $scopeConfig
    )
    {
        $this->_comingSoonProductFactory = $comingSoonProductFactory;
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->_comingSoonProductFactory->create()->setData(
            ['category_id' => $params['category_id'], 'email' => $params['notify-email']])->save();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('/');
        return $resultRedirect;
    }
}