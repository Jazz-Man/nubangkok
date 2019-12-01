<?php

namespace Encomage\ProductNotification\Controller\Ajax;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Encomage\ProductNotification\Model\ProductNotificationFactory;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class Index.
 */
class Index extends Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var ProductNotificationFactory
     */
    protected $productNotificationFactory;

    /**
     * Index constructor.
     *
     * @param Context                    $context
     * @param JsonFactory                $resultJsonFactory
     * @param ProductNotificationFactory $productNotificationFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ProductNotificationFactory $productNotificationFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->productNotificationFactory = $productNotificationFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     *
     * @throws \Exception
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        if ($this->getRequest()->isAjax()) {
            $params = $this->getRequest()->getParams();
            $model = $this->productNotificationFactory->create();
            $model->setData($params)->save();

            return $result->setData($params);
        }
    }
}
