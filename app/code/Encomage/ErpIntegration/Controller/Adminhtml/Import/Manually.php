<?php

namespace Encomage\ErpIntegration\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Encomage\ErpIntegration\Model\Api\Product as ErpIntegrationProduct;
use Encomage\ErpIntegration\Helper\Data;

class Manually extends Action
{
    const ITERATION_PAGE_LIMIT = 1;

    /**
     * @var Product
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
        $page = (int)$this->getRequest()->getParam('p', 0);
        try {
            $i = 0;
            while ($i < self::ITERATION_PAGE_LIMIT) {
                $apiData = $this->erpIntegrationProduct->getDataFromApi($page);
                if (empty($apiData)) {
                    $this->messageManager->addSuccessMessage('Products was imported');
                    return $resultRedirect->setPath('catalog/product');
                }
                $this->erpIntegrationProduct->createProducts($apiData);
                $i++;
                $page++;
            }
            return $resultRedirect->setPath('erp/import/manually',['p' => $page]);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('catalog/product');
        }

        return $resultRedirect;
    }
}