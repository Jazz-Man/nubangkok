<?php

namespace Encomage\Checkout\Rewrite\Magento\ConfigurableProduct\Block\Cart\Item\Renderer;

use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable as ConfigurableAlias;
use Magento\Framework\App\ObjectManager;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;

/**
 * Class Renderer.
 */
class Configurable extends ConfigurableAlias
{
    /**
     * @return array
     */
    public function colorOption(): array
    {
        $options = $this->getOptionList();

        $colorInfo = [];
        foreach ($options as $kay => $option) {
            $colorInfo[$kay] = $option;
            if ('Color' === $option['label'] && isset($option['option_value'])) {
                $colorInfo[$kay]['rgb_code'] = $this->getColorRgbCode($option['option_value']);
            }
        }

        return $colorInfo;
    }

    /**
     * @param $colorCode
     *
     * @return string|null
     */
    protected function getColorRgbCode($colorCode)
    {
        $objectManager = ObjectManager::getInstance();

        $swatchCollection = $objectManager->create(Collection::class);

        $swatchCollection->addFilterByOptionsIds([$colorCode]);
        /** @var \Magento\Swatches\Model\Swatch $item */
        $item = $swatchCollection->getFirstItem();

        return $item->offsetGet('value');
    }
}
