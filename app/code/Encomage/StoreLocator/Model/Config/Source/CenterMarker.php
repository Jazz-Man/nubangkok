<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Model\Config\Source;

/**
 * Class CenterMarker
 * @package Encomage\StoreLocator\Model\Config\Source
 */
class CenterMarker extends \Encomage\StoreLocator\Model\Config\Source\Markers
{
    const SELECT_CENTER_MARKER_LABEL = '--Select center marker--';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $widgetMarkers = $this->_getWidgetMarkers();
            if ($widgetMarkers) {
                $collection = $this->_markersCollection->addFieldToFilter(
                    'entity_id', ['in' => $widgetMarkers]
                );
                $this->_options[] = [
                    'value' => '',
                    'label' => __(self::SELECT_CENTER_MARKER_LABEL)
                ];
                $this->_options = array_merge($this->_options, $collection->toOptionArray());
            }
        }
        return $this->_options;
    }
}