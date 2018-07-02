<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Model\Config\Source;

/**
 * Class Markers
 * @package Encomage\StoreLocator\Model\Config\Source
 */
class Markers implements \Magento\Framework\Option\ArrayInterface
{

    const ALL_STORE_VIEWS = '0';

    /**
     * @var \Encomage\StoreLocator\Model\ResourceModel\Marker\Collection
     */
    protected $_markersCollection;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var array
     */
    protected $_storeIds = [];

    /**
     * @var bool
     */
    protected $_checkChosenInWidget = false;

    /**
     * @var array
     */
    protected $_options = [];

    /**
     * @var array
     */
    protected $_markersByStore = [];


    /**
     * Markers constructor.
     * @param \Encomage\StoreLocator\Model\ResourceModel\Marker\CollectionFactory $markersCollectionFactory
     */
    public function __construct(
        \Encomage\StoreLocator\Model\ResourceModel\Marker\CollectionFactory $markersCollectionFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\Registry $coreRegistry
    )
    {
        $this->_markersCollection = $markersCollectionFactory->create();
        $this->_systemStore = $systemStore;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $stores = $this->_getStoresIds();
            if ($stores) {
                foreach ($stores as $store) {
                    if (!$store instanceof \Magento\Store\Model\Store) {
                        $store = $this->_systemStore->getStoreData($store);
                    }
                    $marker = $this->_getMarkersByStore($store->getId());
                    if ($marker) {
                        $this->_options[] = [
                            'label' => __($store->getName()),
                            'value' => $marker
                        ];
                    }
                }
            }
        }
        return $this->_options;
    }

    /**
     * @param array $storeIds
     * @return $this
     */
    public function setStoresIds(array $storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setCheckChosenInWidget($flag = false)
    {
        if (!$this->_getCurrentWidget()) {
            $flag = false;
        }
        $this->_checkChosenInWidget = $flag;
        return $this;
    }

    /**
     * @return array
     */
    protected function _getStoresIds()
    {
        if (empty($this->_storeIds)) {
            $widget = $this->_getCurrentWidget();
            if ($widget && $widget->getStoreIds()) {
                $this->_storeIds = $widget->getStoreIds();
            }
        }
        if (in_array(self::ALL_STORE_VIEWS, $this->_storeIds)) {
            return $this->_systemStore->getStoreCollection();
        }

        //TODO:: fix it
        if(!$this->_storeIds)
        {
            return $this->_systemStore->getStoreCollection();
        }
    }

    /**
     * @param null $storeId
     * @return array|mixed|null
     */
    protected function _getMarkersByStore($storeId = null)
    {
        if (!$this->_markersByStore) {
            $widgetMarkers = ($this->_checkChosenInWidget) ? $this->_getWidgetMarkers() : [];
            foreach ($this->_markersCollection as $marker) {
                $markerStores = $marker->getStoreIds();
                foreach ($markerStores as $store) {
                    $markerOptions = [
                        'label' => __($marker->getName()),
                        'value' => $marker->getId()
                    ];
                    if ($this->_checkChosenInWidget && in_array($marker->getId(), $widgetMarkers)) {
                        $markerOptions['selected'] = true;
                    }
                    $this->_markersByStore[$store][] = $markerOptions;
                }
            }
        }
        if ($storeId) {
            return isset($this->_markersByStore[$storeId]) ? $this->_markersByStore[$storeId] : null;
        }
        return $this->_markersByStore;
    }

    /**
     * @return array
     */
    protected function _getWidgetMarkers()
    {
        $widget = $this->_getCurrentWidget();
        if (!$widget) {
            return [];
        }
        $params = $widget->getWidgetParameters();
        return (isset($params['markers'])) ? $params['markers'] : [];
    }

    /**
     * @return null | \Magento\Widget\Model\Widget\Instance
     */
    protected function _getCurrentWidget()
    {
        return $this->_coreRegistry->registry('current_widget_instance');
    }
}
