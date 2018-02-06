<?php

namespace Encomage\Cms\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;


class UpgradeData implements UpgradeDataInterface
{

    protected $_pageFactory;
    protected $_blockFactory;


    public function __construct(
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\BlockFactory $blockFactory
    ) {
        $this->_pageFactory = $pageFactory;
        $this->_blockFactory = $blockFactory;
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
                'content' =>$content
            ];
            $this->_blockFactory->create()->setData($testBlock)->save();
        }
    }
}
