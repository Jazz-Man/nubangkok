<?php

namespace Encomage\DropdownFields\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class Country
 * @package Encomage\DropdownFields\Model\ResourceModel
 */
class Country extends AbstractDb
{

    protected function _construct()
    {
        $this->_init('encomage_country_region_city_table', 'entity_id');
    }

    /**
     * @param string $countryCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRegionBycCountryCode(string $countryCode)
    {
        $connection = $this->getConnection();
        $sql = $connection
            ->select()
            ->from($this->getMainTable(), 'region')
            ->where('country_code = ? ', $countryCode);


        return $connection->fetchCol($sql);
    }

    /**
     * @param string $countryCode
     * @param string $region
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCityByRegion(string $region, string $countryCode)
    {
        $connection = $this->getConnection();
        $where = 'region = \'' . $region . '\' AND ' . 'country_code=\'' . $countryCode . '\'';
        $sql = $connection
            ->select()
            ->from($this->getMainTable(), 'city')
            ->where($where);

        return $connection->fetchCol($sql);
    }
}