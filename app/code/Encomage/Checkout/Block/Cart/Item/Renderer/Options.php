<?php

namespace Encomage\Checkout\Block\Cart\Item\Renderer;

use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttributeResource;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

/**
 * Class Options
 * @package Encomage\Checkout\Block\Cart\Item\Renderer
 */
class Options extends \Magento\Framework\View\Element\Template
{
    /**
     * @var EavAttributeResource
     */
    private $attributeResource;

    /**
     * @var AttributeFactory
     */
    private $attributeFactory;

    /**
     * @var SwatchCollectionFactory
     */
    private $swatchCollectionFactory;

    /**
     * @var
     */
    protected $attributeOrder;

    /**
     * @var
     */
    protected $_rgbColors;

    /**
     * Options constructor.
     * @param Template\Context $context
     * @param EavAttributeResource $attributeResource
     * @param AttributeFactory $attributeFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        EavAttributeResource $attributeResource,
        AttributeFactory $attributeFactory,
        SwatchCollectionFactory $swatchCollectionFactory,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->setTemplate('Magento_Checkout::cart/item/renderer/options.phtml');
        $this->attributeResource = $attributeResource;
        $this->attributeFactory = $attributeFactory;
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        $this->attributeOrder['size'] = 1;
        $this->attributeOrder['color'] = 2;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_sortOptions($this->getData('options'));
    }

    /**
     * @param $colorCodeId
     * @return null
     */
    public function getColorRgbCode($colorCodeId)
    {
        $swatchCollection = $this->swatchCollectionFactory->create();
        $swatchCollection->addFilterByOptionsIds([$colorCodeId]);
        $item = $swatchCollection->getFirstItem();
        return $item->getValue() ? $item->getValue() : null;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function _sortOptions(array $options)
    {
        $result = [];
        foreach ($options as $index => $option) {
            $attribute = $this->_getAttribute($option['option_id']);
            $key = (isset($this->attributeOrder[$attribute->getAttributeCode()]))
                ? $this->attributeOrder[$attribute->getAttributeCode()]
                : $this->attributeOrder[end(array_keys($this->attributeOrder))]++;
            $result[$key] = $options[$index];
            $result[$key]['att_code'] = $attribute->getAttributeCode();;
        }
        ksort($result);
        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    protected function _getAttribute($id)
    {
        $attribute = $this->attributeFactory->create();
        $this->attributeResource->load($attribute, $id);
        return $attribute;
    }
}