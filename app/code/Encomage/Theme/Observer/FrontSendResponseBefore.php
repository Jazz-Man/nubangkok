<?php
namespace Encomage\Theme\Observer;
/**
 * Class FrontSendResponseBefore
 * @package Encomage\Theme\Observer
 */
class FrontSendResponseBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonSerializer;

    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $_layout;

    /**
     * FrontSendResponseBefore constructor.
     * @param \Magento\Framework\View\Layout $layout
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
     */
    public function __construct(
        \Magento\Framework\View\Layout $layout,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer
    )
    {
        $this->_layout = $layout;
        $this->_request = $request;
        $this->_jsonSerializer = $jsonSerializer;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ((bool)$this->_request->getParam('ajax_get_block', false) && $this->_request->isAjax()) {
            /** @var \Magento\Framework\View\Element\Template $listBlock */
            if ($block = $this->_layout->getBlock($this->_request->getParam('ajax_get_block', false))) {
                $observer->getEvent()->getResponse()->representJson(
                    $this->_jsonSerializer->serialize(['html' => $block->toHtml()])
                );
            }
        }
    }
}