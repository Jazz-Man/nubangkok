<?php

namespace Encomage\Swatches\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Swatches\Model\Swatch;

/**
 * Class EavAttribute
 * @package Encomage\Swatches\Model\Plugin
 */
class EavAttribute extends \Magento\Swatches\Model\Plugin\EavAttribute
{
    /**
     * @var \Magento\Swatches\Model\ResourceModel\Swatch
     */
    protected $swatchResource;

    /**
     * @var Json
     */
    protected $serializer;

    /**
     * EavAttribute constructor.
     * @param \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory
     * @param \Magento\Swatches\Model\SwatchFactory $swatchFactory
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param Json|null $serializer
     * @param \Magento\Swatches\Model\ResourceModel\Swatch $swatchResource
     */
    public function __construct(
        \Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory $collectionFactory,
        \Magento\Swatches\Model\SwatchFactory $swatchFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        Json $serializer = null,
        \Magento\Swatches\Model\ResourceModel\Swatch $swatchResource
    )
    {
        parent::__construct($collectionFactory, $swatchFactory, $swatchHelper, $serializer);
        $this->swatchResource = $swatchResource;
        $this->serializer = $serializer;
    }

    /**
     * @param Attribute $attribute
     */
    protected function convertSwatchToDropdown(Attribute $attribute)
    {
        if ($attribute->getData(Swatch::SWATCH_INPUT_TYPE_KEY) == Swatch::SWATCH_INPUT_TYPE_DROPDOWN) {
            $additionalData = $attribute->getData('additional_data');
            if (!empty($additionalData)) {
                $additionalData = $this->serializer->unserialize($additionalData);
                if (is_array($additionalData) && isset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY])) {
                    $this->cleanEavAttributeOptionSwatchValues($attribute->getOption());
                    unset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY]);
                    $attribute->setData('additional_data', $this->serializer->serialize($additionalData));
                }
            }
        }
    }

    /**
     * @param $attributeOptions
     * @param null $swatchType
     */
    private function cleanEavAttributeOptionSwatchValues($attributeOptions, $swatchType = null)
    {
        if (count($attributeOptions) && isset($attributeOptions['value'])) {
            $optionsIDs = array_keys($attributeOptions['value']);
            $this->swatchResource->clearSwatchOptionByOptionIdAndType($optionsIDs, $swatchType);
        }
    }

    /**
     * @param $attributeOptions
     */
    private function cleanTextSwatchValuesAfterSwitch($attributeOptions)
    {
        $this->cleanEavAttributeOptionSwatchValues($attributeOptions, Swatch::SWATCH_TYPE_TEXTUAL);
    }

}