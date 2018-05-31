<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Block;

/**
 * Class MapAbstract
 * @package Encomage\StoreLocator\Block
 */
class StoreLocationPage extends \Encomage\StoreLocator\Block\MapAbstract
{
    /**
     * @var \Encomage\StoreLocator\Model\ResourceModel\Marker\Collection
     */

    protected $_collection;

    /**
     * @return $this|\Encomage\StoreLocator\Model\ResourceModel\Marker\Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = $this->_markersCollection->getDataByStore();
        }
        return $this->_collection;
    }

    /**
     * @return mixed
     */
    public function getNoMarkersLabel()
    {
        return __('There are no added markers.');
    }
}
