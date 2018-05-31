<?php
/**
 * @author Andrey Bondarenko
 * @link http://encomage.com
 * @mail info@encomage.com
 */

namespace Encomage\StoreLocator\Model\ResourceModel;

/**
 * Class Marker
 * @package Encomage\StoreLocator\Model\ResourceModel
 */
class Marker extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Class construct
     */
    protected function _construct()
    {
        $this->_init('encomage_storelocator', 'entity_id');
    }

    /**
     * @param \Encomage\StoreLocator\Model\Marker $object
     * @return $this
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markersDelete(\Encomage\StoreLocator\Model\Marker $object)
    {
        if (!$object->hasData('ids')) {
            throw new \Magento\Framework\Exception\InputException(__('IDs required'));
        }
        $this->getConnection()
            ->delete(
                $this->getMainTable(),
                $this->getConnection()->prepareSqlCondition(
                    'entity_id',
                    ['in' => $object->getData('ids')]
                )
            );
        return $this;
    }
}