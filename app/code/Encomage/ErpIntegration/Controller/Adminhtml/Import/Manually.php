<?php

namespace Encomage\ErpIntegration\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Encomage\ErpIntegration\Model\Api\Product;

class Manually extends Action
{
    protected $product;

    public function __construct(Action\Context $context, Product $product)
    {
        parent::__construct($context);
        $this->product = $product;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        $productImport =$this->product->importAllProducts();
        if(!$productImport){
            $this->messageManager->addErrorMessage('Something Wrong');
            return $resultRedirect;
        }
        $this->messageManager->addSuccessMessage('Product was imported');
        return $resultRedirect;
    }
}