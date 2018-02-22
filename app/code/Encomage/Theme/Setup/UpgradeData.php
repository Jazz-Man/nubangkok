<?php

namespace Encomage\Theme\Setup;

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
            $this->enableWebSeoUrlRewrites();
            $this->changeStoreEmailAddresses();
        }
    }

    private function enableWebSeoUrlRewrites()
    {
        $this->configResource->saveConfig(
            'web/seo/use_rewrites',
            1,
            'default',
            0
        );
    }

    private function changeStoreEmailAddresses()
    {
        $data = [
            'trans_email/ident_support/email' => 'bangdok@example.com',
            'trans_email/ident_support/name' => 'nuBangkok',
            'trans_email/ident_custom1/email' => 'bangdok@example.com',
            'trans_email/ident_custom1/name' => 'nuBangkok',
            'trans_email/ident_custom2/email' => 'bangdok@example.com',
            'trans_email/ident_custom2/name' => 'nuBangkok',
            'trans_email/ident_general/email' => 'bangdok@example.com',
            'trans_email/ident_general/name' => 'nuBangkok',
            'trans_email/ident_sales/email' => 'bangdok@example.com',
            'trans_email/ident_sales/name' => 'nuBangkok',
        ];
        foreach ($data as $path => $value) {
            $this->configResource->saveConfig(
                $path,
                $value,
                'default',
                0
            );
        }
    }
}
