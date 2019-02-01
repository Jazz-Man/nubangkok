<?php

namespace Encomage\Catalog\Plugin\Block\Product\Renderer;

use Magento\Swatches\Block\Product\Renderer\Configurable as Subject;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Registry;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Encomage\Catalog\Helper\Config as Helper;

/**
 * Class Configurable
 *
 * @package Encomage\Catalog\Plugin\Block\Product\Renderer
 */
class Configurable
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Attribute
     */
    private $eavAttribute;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * Configurable constructor.
     *
     * @param Json $json
     * @param Registry $registry
     * @param Attribute $eavAttribute
     * @param Helper $helper
     */
    public function __construct(
        Json $json,
        Registry $registry,
        Attribute $eavAttribute,
        Helper $helper
    ) {
        $this->json = $json;
        $this->registry = $registry;
        $this->eavAttribute = $eavAttribute;
        $this->helper = $helper;
    }

    /**
     * @param Subject $subject
     * @param $result
     * @return mixed
     */
    public function afterGetJsonSwatchConfig(Subject $subject, $result)
    {
        $data = $this->json->unserialize($result);
        if ($this->helper->isUseSimpleInsteadConfigurable()) {
            $product = $this->registry->registry('simple_product');
            if ($product) {
                $attributeId = $this->eavAttribute->getIdByCode('catalog_product', 'color');
                $data['productColorData'] = ['colorId' => $attributeId, 'colorValue' => $product->getColor()];
            }
        } else {
            $data['productColorData'] = false;
        }

        return $this->json->serialize($data);
    }
}