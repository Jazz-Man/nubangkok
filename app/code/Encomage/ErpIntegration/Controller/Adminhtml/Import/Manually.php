<?php

namespace Encomage\ErpIntegration\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Encomage\ErpIntegration\Model\Api\Product;
use Encomage\ErpIntegration\Helper\Data;

class Manually extends Action
{
    /**
     * @var Product
     */
    protected $product;

    /**
     * Manually constructor.
     *
     * @param Action\Context $context
     * @param Product $product
     * @param Data $data
     */
    public function __construct(Action\Context $context, Product $product, Data $data)
    {
        parent::__construct($context);
        $this->product = $product;
        $this->_helper = $data;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        set_time_limit($this->_helper->getTimeLimit());
        try {
            $this->product->importAllProducts();
            $this->messageManager->addSuccessMessage('Products was imported');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        ini_restore('max_execution_time');

        return $resultRedirect;
    }
}