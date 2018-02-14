<?php

namespace Encomage\Wishlist\Block\Customer\Wishlist\Item;

use Magento\Catalog\Block\Product\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

class Option extends \Magento\Wishlist\Block\Customer\Wishlist\Item\Options
{
    /**
     * @var SwatchCollectionFactory
     */
    private $swatchCollectionFactory;

    /**
     * Option constructor.
     * @param Context $context
     * @param HttpContext $httpContext
     * @param ConfigurationPool $helperPool
     * @param SwatchCollectionFactory $swatchCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        HttpContext $httpContext,
        ConfigurationPool $helperPool,
        SwatchCollectionFactory $swatchCollectionFactory,
        array $data = [])
    {
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        parent::__construct(
            $context,
            $httpContext,
            $helperPool,
            $data);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Wishlist::item/column/option.phtml');
    }

    /**
     * @return array
     */
    public function getOption()
    {
        $options = [];
        if ($this->hasData('product_att')) {
            foreach ($this->getConfiguredOptions() as $value) {
                if ($value['label'] == $this->getData('product_att')) {
                    $options = $value;
                    break;
                }
            }
        }

        if ($options['label'] == 'Color') {
            $options['rgb_code'] = $this->getColorRgbCode($options['option_value']);
        }
        return $options;
    }

    /**
     * @return string
     */
    public function getCssClass()
    {
        $cssClass = '';
        if ($this->hasData('product_att')) {
            $cssClass .= mb_strtolower($this->getData('product_att'));
        }
        if ($this->hasData('css_class')) {
            $cssClass .= ' ' . $this->getData('css_class');
        }
        return $cssClass;
    }

    /**
     * @param $colorCodeId
     * @return null
     */
    protected function getColorRgbCode($colorCodeId)
    {
        $swatchCollection = $this->swatchCollectionFactory->create();
        $swatchCollection->addFilterByOptionsIds([$colorCodeId]);
        $item = $swatchCollection->getFirstItem();
        return $item->getValue() ? $item->getValue() : null;
    }

}