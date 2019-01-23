<?php

namespace Encomage\DropdownFields\Override\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as ParentLayoutProcessor;
use Magento\Customer\Model\AttributeMetadataDataProvider;
use Magento\Ui\Component\Form\AttributeMapper;
use \Magento\Checkout\Block\Checkout\AttributeMerger;
use Encomage\DropdownFields\Helper\Data as Helper;

/**
 * Class LayoutProcessor
 * @package Encomage\DropdownFields\Override\Block\Checkout'
 */
class LayoutProcessor extends ParentLayoutProcessor
{
    const COMPONENT_REGION_NAME = 'Eadesigndev_RomCity/js/form/element/region';
    const COMPONENT_CITY_NAME = 'Eadesigndev_RomCity/js/form/element/city';

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * LayoutProcessor constructor.
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param AttributeMapper $attributeMapper
     * @param AttributeMerger $merger
     * @param Helper $helper
     */
    public function __construct(
        AttributeMetadataDataProvider $attributeMetadataDataProvider,
        AttributeMapper $attributeMapper,
        AttributeMerger $merger,
        Helper $helper
    )
    {
        parent::__construct($attributeMetadataDataProvider, $attributeMapper, $merger);
        $this->helper = $helper;
    }

    /**
     * @param array $jsLayout
     * @return array
     */
    public function process($jsLayout)
    {
        $process = parent::process($jsLayout);
        $shippingAddressChildren = $process['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'];
        if (isset($shippingAddressChildren['region_id'])) {
            $regionComponent = $shippingAddressChildren['region_id'];
            if ($regionComponent['component'] === self::COMPONENT_REGION_NAME) {
                $process['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['region_id']['config'] = $this->mergeConfigData($regionComponent['config']);
            }
        }
        if (isset($shippingAddressChildren['city_id'])) {
            $cityComponent = $shippingAddressChildren['city_id'];
            if ($cityComponent['component'] === self::COMPONENT_CITY_NAME) {
                $cityComponent = $shippingAddressChildren['region_id'];
                $process['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children']
                ['city_id']['config'] = $this->mergeConfigData($cityComponent['config']);
            }
        }

        return $process;
    }

    /**
     * @param array $componentConfigData
     * @return array
     */
    private function mergeConfigData(array $componentConfigData)
    {
        $componentConfigData = array_merge($componentConfigData, [
            'urlApiCity' => $this->helper->getApiCityDomainName(),
            'urlApiRegion' => $this->helper->getApiRegionDomainName(),
            'apiKey' => $this->helper->getApiKeyValue()
        ]);

        return $componentConfigData;
    }

}