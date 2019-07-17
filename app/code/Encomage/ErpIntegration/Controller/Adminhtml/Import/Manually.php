<?php

namespace Encomage\ErpIntegration\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Encomage\ErpIntegration\Model\Api\Product as ErpIntegrationProduct;
use Encomage\ErpIntegration\Helper\Data;

/**
 * Class Manually
 *
 * @package Encomage\ErpIntegration\Controller\Adminhtml\Import
 */
class Manually extends Action
{
    const ITERATION_PAGE_LIMIT = 3;


    /**
     * @var \Encomage\ErpIntegration\Model\Api\Product
     */
    protected $erpIntegrationProduct;

    /**
     * Manually constructor.
     *
     * @param Action\Context $context
     * @param ErpIntegrationProduct $erpIntegrationProduct
     * @param Data $data
     */
    public function __construct(Action\Context $context, ErpIntegrationProduct $erpIntegrationProduct, Data $data)
    {
        parent::__construct($context);
        $this->erpIntegrationProduct = $erpIntegrationProduct;
        $this->_helper = $data;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $page = (int)$this->getRequest()->getParam('p', 1);
        try {
            $apiData = $this->erpIntegrationProduct->getDataFromApi($page);
            if (empty($apiData)) {
                $this->messageManager->addSuccessMessage('Products was imported');
                return $resultRedirect->setPath('catalog/product');
            }
            while (count($apiData)) {
                $this->erpIntegrationProduct->createProducts($apiData);
                $apiData = $this->erpIntegrationProduct->getDataFromApi(++$page);
            }

            $this->messageManager->addSuccessMessage('Products was imported');

            return $resultRedirect->setPath('catalog/product');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('catalog/product', ['p' => $page]);
    }
}