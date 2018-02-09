<?php

namespace Encomage\Checkout\Setup;

use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


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
}
