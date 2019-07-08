<?php

namespace Encomage\ErpIntegration\Block\Adminhtml\Form\Renderer;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Store\Model\Store;

/**
 * Class SelectOptions.
 */
class SelectOptions extends Select
{

    public $color_attributes = [];

    /**
     * SelectOptions constructor.
     *
     * @param Context $context
     * @param Config  $eavConfig
     * @param array   $data
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);

        try {
            /** @var array $color_attributes */
            $color_attributes = $eavConfig->getAttribute(Product::ENTITY, 'color')
                                          ->setStoreId(Store::DEFAULT_STORE_ID)
                                          ->getSource()
                                          ->getAllOptions();

            uasort($color_attributes, static function ($a, $b) {
                return strcmp($a['label'], $b['label']);
            });

            $this->color_attributes = $color_attributes;

            $this->setOptions($this->color_attributes);
        } catch (LocalizedException $e) {
        }
    }

    /**
     * @param string $value
     *
     * @return \Encomage\ErpIntegration\Block\Adminhtml\Form\Renderer\SelectOptions
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * @param string $value
     *
     * @return \Encomage\ErpIntegration\Block\Adminhtml\Form\Renderer\SelectOptions
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }
}
