<?php

namespace Encomage\Checkout\Setup;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail;


class UpgradeData implements UpgradeDataInterface
{
    private $configResource;

    public function __construct(Config $configResource)
    {
        $this->configResource = $configResource;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->disableGuestCheckout();
        }
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->changeThumbnailInShoppingCart();
        }
    }

    private function disableGuestCheckout()
    {
        $this->configResource->saveConfig(
            \Magento\Checkout\Helper\Data::XML_PATH_GUEST_CHECKOUT,
            0,
            'default',
            0
        );
    }

    private function changeThumbnailInShoppingCart()
    {
        $this->configResource->saveConfig(
            Configurable::CONFIG_THUMBNAIL_SOURCE,
            Thumbnail::OPTION_USE_OWN_IMAGE,
            'default',
            0
        );
    }
}
