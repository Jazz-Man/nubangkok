<?php
namespace Encomage\ErpIntegration\Controller\Adminhtml\Invoice;

use Magento\Backend\App\Action;
use Encomage\ErpIntegration\Model\Api\Invoice as ApiInvoice;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

class Send extends Action
{
    protected $apiInvoice;
    protected $resultFactory;
    protected $orderRepository;

    public function __construct(
        Action\Context $context,
        ApiInvoice $apiInvoice,
        ResultFactory $resultFactory,
        OrderRepositoryInterface $orderRepository
    )
    {
        parent::__construct($context);
        $this->apiInvoice = $apiInvoice;
        $this->resultFactory = $resultFactory;
        $this->orderRepository = $orderRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $orderId = $this->getRequest()->getParam('id');
        
        if (!$orderId) {
            $this->messageManager->addErrorMessage(__('Order ID is not exist'));
        }
        $order = $this->orderRepository->get($orderId);
        
        $result = $this->apiInvoice->createInvoice($order);
        if ($result['returnResult']) {
            $this->messageManager->addSuccessMessage(__('Invoice sent.'));
        } else {
            $this->messageManager->addErrorMessage(__('Invoice wasn\'t sent.'));
        }
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Encomage_ErpIntegration::invoice');
    }
}