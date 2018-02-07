<?php

namespace Encomage\Cms\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


class UpgradeData implements UpgradeDataInterface
{

    private $pageFactory;
    private $blockFactory;
    private $scopeConfig;
    private $configResource;


    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Config\Model\ResourceModel\Config $configResource
    )
    {
        $this->pageFactory = $pageFactory;
        $this->blockFactory = $blockFactory;
        $this->scopeConfig = $scopeConfig;
        $this->configResource = $configResource;
    }

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.3') < 0) {
            $content = '<div class="register-form-right-image"><img src="" alt=""></div>';
            $testBlock = [
                'title' => 'Register Form right',
                'identifier' => 'register-form-right',
                'stores' => [0],
                'is_active' => 1,
                'content' => $content
            ];
            $this->blockFactory->create()->setData($testBlock)->save();
        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->modifyFooterCopyright();
        }
    }

    private function modifyFooterCopyright()
    {
        $this->configResource->saveConfig('design/footer/copyright',
            'nu Bangkok Copyright ©2018. All Rights Reserved',
            'default',
            0
        );

        $this->configResource->saveConfig('design/footer/copyright',
            'nu Bangkok Copyright ©2018. All Rights Reserved',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            1
        );
        return $this;
    }
}
