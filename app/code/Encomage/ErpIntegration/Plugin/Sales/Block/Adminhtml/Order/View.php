<?php
namespace Encomage\ErpIntegration\Plugin\Sales\Block\Adminhtml\Order;

use Magento\Sales\Block\Adminhtml\Order\View as OrderView;
use Magento\Framework\UrlInterface;
use Magento\Framework\AuthorizationInterface;

/**
 * Class View
 *
 * @package Encomage\ErpIntegration\Plugin\Sales\Block\Adminhtml\Order
 */
class View
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    protected $_authorization;

    /**
     * View constructor.
     *
     * @param \Magento\Framework\UrlInterface           $url
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(
        UrlInterface $url,
        AuthorizationInterface $authorization
    ) {
        $this->_urlBuilder = $url;
        $this->_authorization = $authorization;
    }

    /**
     * @param \Magento\Sales\Block\Adminhtml\Order\View $subject
     */
    public function beforeSetLayout(OrderView $subject) {
        $url = $this->_urlBuilder->getUrl('erp/invoice/send', ['id' => $subject->getOrderId()]);

        $subject->addButton(
            'send_invoice',
            [
                'label' => __('Send Invoice'),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class' => 'my-button'
            ]
        );
    }
}