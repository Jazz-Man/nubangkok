<?php
namespace Encomage\ErpIntegration\Controller\Index;

use Magento\Framework\App\Action;
use Encomage\ErpIntegration\Model\Api\Product;
use Magento\Customer\Model\Session;

class Index extends Action\Action
{
    private $product;

    private $customerSession;
    
    public function __construct(
        Action\Context $context,
        Product $product,
        Session $customerSession
    )
    {
        parent::__construct($context);
        $this->product = $product;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
//        $customer = $this->customerSession->getCustomer();
        $result = $this->product->importAllProducts();
        return $result;
    }
}