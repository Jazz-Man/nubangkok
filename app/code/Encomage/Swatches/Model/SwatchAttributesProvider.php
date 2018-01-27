<?php

namespace Encomage\Swatches\Model;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Swatches\Model\SwatchAttributeCodes;
use Magento\Catalog\Model\Product;
use Magento\Swatches\Helper\Data as SwatchesHelper;
use Magento\Framework\App\ObjectManager;

/**
 * Class SwatchAttributesProvider
 * @package Encomage\Swatches\Model
 */
class SwatchAttributesProvider extends \Magento\Swatches\Model\SwatchAttributesProvider
{
    /**
     * @var mixed
     */
    protected $swatchesHelper;

    /**
     * @var Configurable
     */
    protected $typeConfigurable;

    /**
     * @var SwatchAttributeCodes
     */
    protected $swatchAttributeCodes;

    /**
     * @var
     */
    protected $attributesPerProduct;

    /**
     * SwatchAttributesProvider constructor.
     * @param Configurable $typeConfigurable
     * @param SwatchAttributeCodes $swatchAttributeCodes
     * @param SwatchesHelper $swatchHelper
     */
    public function __construct(
        Configurable $typeConfigurable,
        SwatchAttributeCodes $swatchAttributeCodes,
        SwatchesHelper $swatchHelper
    )
    {
        parent::__construct($typeConfigurable, $swatchAttributeCodes);
        $this->typeConfigurable = $typeConfigurable;
        $this->swatchAttributeCodes = $swatchAttributeCodes;
        $this->swatchesHelper = $swatchHelper ?: ObjectManager::getInstance()->create(SwatchesHelper::class);
    }

    /**
     * @param Product $product
     * @return array|Configurable\Attribute[]
     */
    public function provide(Product $product)
    {
        if ($product->getTypeId() !== Configurable::TYPE_CODE) {
            return [];
        }
        if (!isset($this->attributesPerProduct[$product->getId()])) {
            $configurableAttributes = $this->typeConfigurable->getConfigurableAttributes($product);
            $swatchAttributeCodeMap = $this->swatchAttributeCodes->getCodes();
            $swatchAttributes = [];
            foreach ($configurableAttributes as $configurableAttribute) {
                if ($this->swatchesHelper->isSwatchAttribute($configurableAttribute->getProductAttribute())) {
                    if (array_key_exists($configurableAttribute->getAttributeId(), $swatchAttributeCodeMap)) {
                        $swatchAttributes[$configurableAttribute->getAttributeId()]
                            = $configurableAttribute->getProductAttribute();
                    }
                }
            }
            $this->attributesPerProduct[$product->getId()] = $swatchAttributes;
        }
        return $this->attributesPerProduct[$product->getId()];
    }
}