<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Controller\Adminhtml\Widget;

/**
 * Class AjaxMarkers
 * @package Encomage\StoreLocator\Controller\Adminhtml\Widget
 */
class AjaxMarkers extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var \Encomage\StoreLocator\Model\Config\Source\Markers
     */
    protected $_markersConfigSource;

    /**
     * AjaxMarkers constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Math\Random $mathRandom
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Encomage\StoreLocator\Model\Config\Source\Markers $markers
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Encomage\StoreLocator\Model\Config\Source\Markers $markers
    )
    {
        parent::__construct($context, $coreRegistry, $widgetFactory, $logger, $mathRandom, $translateInline);
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_markersConfigSource = $markers;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $stores = $this->getRequest()->getParam('stores', []);
        $result = ['error' => true];
        $resultJson = $this->_resultJsonFactory->create();
        if ($this->getRequest()->getParam('isAjax') && !empty($stores)) {
            $this->_initWidgetInstance();
            $this->_markersConfigSource->setStoresIds($stores);
            $this->_markersConfigSource->setCheckChosenInWidget(true);
            $result['markers'] = $this->_markersConfigSource->toOptionArray();
            $result['error'] = false;
        }
        return $resultJson->setData($result);
    }
}