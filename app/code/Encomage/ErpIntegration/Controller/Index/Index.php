<?php

namespace Encomage\ErpIntegration\Controller\Index;

use Magento\Framework\App\Action\Context;
use Encomage\ErpIntegration\Model\Api\Product;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $product;

    public function __construct(Context $context, Product $product)
    {
        $this->product = $product;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->product->importAllProducts();
    }
}