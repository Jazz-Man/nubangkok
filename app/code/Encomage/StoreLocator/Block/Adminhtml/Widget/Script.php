<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Block\Adminhtml\Widget;

/**
 * Class Script
 * @package Encomage\StoreLocator\Block\Adminhtml\Widget
 */
class Script extends \Magento\Backend\Block\Template
{
    const MARKERS_AJAX_URL = 'storelocator/widget/ajaxMarkers';

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Script constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data
    )
    {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return [
            'ajaxUrl'               => $this->getUrl(self::MARKERS_AJAX_URL),
            'widgetId'              => $this->_getCurrentWidget()->getInstanceId(),
            'widgetCode'            => $this->_getCurrentWidget()->getInstanceCode(),
            'centerMarkerLabel'     => $this->_getLabelForCenterMarkerSelect(),
            'selectedCenterMarker'  => $this->_getSelectedCenterMarker()
        ];
    }

    /**
     * @return mixed
     */
    protected function _getLabelForCenterMarkerSelect()
    {
        return __(\Encomage\StoreLocator\Model\Config\Source\CenterMarker::SELECT_CENTER_MARKER_LABEL);
    }

    /**
     * @return int
     */
    protected function _getSelectedCenterMarker()
    {
        $widgetParams = $this->_coreRegistry->registry('current_widget_instance')->getWidgetParameters();
        return (int)(isset($widgetParams['center_marker'])) ? $widgetParams['center_marker'] : 0;
    }

    /**
     * @return null|\Magento\Widget\Model\Widget\Instance
     */
    protected function _getCurrentWidget()
    {
        return $this->_coreRegistry->registry('current_widget_instance');
    }
}