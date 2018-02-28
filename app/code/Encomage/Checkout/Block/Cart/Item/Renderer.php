<?php

namespace Encomage\Checkout\Block\Cart\Item;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory as SwatchCollectionFactory;

class Renderer extends \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable
{
    /**
     * @var SwatchCollectionFactory
     */
    private $swatchCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Helper\Product\Configuration $productConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Module\Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        SwatchCollectionFactory $swatchCollectionFactory,
        array $data
    )
    {
        $this->swatchCollectionFactory = $swatchCollectionFactory;
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,

            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
    }

    /**
     * @return array
     */
    public function colorOption()
    {
        $options = $this->getOptionList();
        $colorInfo = [];
        foreach ($options as $kay => $option) {
            $colorInfo[$kay] = $option;
            if ($option['label'] == 'Color' && isset($option['option_value'])) {
                $colorInfo[$kay]['rgb_code'] = $this->getColorRgbCode($option['option_value']);
            }
        }
        return $colorInfo;
    }

    /**
     * @param $colorCode
     * @return null
     */
    protected function getColorRgbCode($colorCode)
    {
        $swatchCollection = $this->swatchCollectionFactory->create();
        $swatchCollection->addFilterByOptionsIds([$colorCode]);
        $item = $swatchCollection->getFirstItem();
        return $item->getValue() ? $item->getValue() : null;
    }
}