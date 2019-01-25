<?php

namespace Encomage\DropdownFields\Override\Helper;

use Eadesigndev\RomCity\Helper\CitiesJsonRomCity as ParentCitiesJsonRomCity;
use Eadesigndev\RomCity\Model\RomCityRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Cache\Type\Config;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Json\Helper\Data;
use \Magento\Directory\Model\CurrencyFactory;

/**
 * Class CitiesJsonRomCity
 * @package Encomage\DropdownFields\Override\Helper
 */
class CitiesJsonRomCity extends ParentCitiesJsonRomCity
{
    /**
     * CitiesJsonRomCity constructor.
     * @param Context $context
     * @param Config $configCacheType
     * @param Collection $countryCollection
     * @param CollectionFactory $regCollectionFactory
     * @param Data $jsonHelper
     * @param StoreManagerInterface $storeManager
     * @param CurrencyFactory $currencyFactory
     * @param RomCityRepository $romCityRepository
     * @param SearchCriteriaBuilder $searchCriteria
     */
    public function __construct(
        Context $context,
        Config $configCacheType,
        Collection $countryCollection,
        CollectionFactory $regCollectionFactory,
        Data $jsonHelper,
        StoreManagerInterface $storeManager,
        CurrencyFactory $currencyFactory,
        RomCityRepository $romCityRepository,
        SearchCriteriaBuilder $searchCriteria)
    {
        parent::__construct(
            $context,
            $configCacheType,
            $countryCollection,
            $regCollectionFactory,
            $jsonHelper,
            $storeManager,
            $currencyFactory,
            $romCityRepository,
            $searchCriteria
        );
    }

    /**
     * Retrieve regions data
     *
     * @return array
     */
    public function getRegionData()
    {
        return [];
    }

}