<?php
namespace Encomage\ErpIntegration\Controller\Index;
use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Encomage\ErpIntegration\Model\Api\Customer as ERP;

class Index extends Action\Action
{
    private $resultPageFactory;

    private $erp;

    public function __construct(Action\Context $context, PageFactory $resultPageFactory, ERP $erp)
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->erp = $erp;
    }

    public function execute()
    {
        $result = $this->erp->getAllCustomers();
        print_r($result);
    }
}