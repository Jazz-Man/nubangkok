<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Model;

/**
 * Class Marker
 * @package Encomage\StoreLocator\Model
 */
class Marker extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Class construct
     */
    protected function _construct()
    {
        $this->_init('Encomage\StoreLocator\Model\ResourceModel\Marker');
        $this->_eventPrefix = 'storelocator_markers';
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveMarker()
    {
        $this->_getResource()->save($this);
        return $this;
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function massMarkersDelete()
    {
        $this->_getResource()->markersDelete($this);
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteMarker()
    {
        $this->setIds([$this->getEntityId()]);
        $this->_getResource()->markersDelete($this);
        return $this;
    }

    /**
     * @param $entityId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadMarkerById($entityId)
    {
        $this->_getResource()->load($this, $entityId, $this->getIdFieldName());
        return $this;
    }

    /**
     * @param array $dataFromRequest
     * @return array
     */
    public function validateData(array $dataFromRequest)
    {
        $errors = [];
        if (!isset($dataFromRequest['name']) || empty($dataFromRequest['name'])) {
            $errors[] = __('Name is required field.');
        }
        if (!isset($dataFromRequest['latitude']) || empty($dataFromRequest['latitude'])) {
            $errors[] = __('Latitude is required field.');
        }
        if (!isset($dataFromRequest['longitude']) || empty($dataFromRequest['longitude'])) {
            $errors[] = __('Longitude is required field.');
        }
        if (!isset($dataFromRequest['store_id']) || empty($dataFromRequest['store_id'])) {
            $errors[] = __('Store Views is required field.');
        }
        return $errors;
    }

    /**
     * @return mixed
     */
    public function getStoreIds()
    {
        if (is_string($this->getData('store_id'))) {
            return explode(',', $this->getData('store_id'));
        }
        return $this->getData('store_id');
    }
}