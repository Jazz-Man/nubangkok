<?php

namespace Encomage\ErpIntegration\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Encomage\ErpIntegration\Model\Api\Product;

class Manually extends Action
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * Manually constructor.
     * @param Action\Context $context
     * @param Product $product
     */
    public function __construct(Action\Context $context, Product $product)
    {
        parent::__construct($context);
        $this->product = $product;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        try {
            $this->product->importAllProducts();
            $this->messageManager->addSuccessMessage('Products was imported');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect;
    }
}