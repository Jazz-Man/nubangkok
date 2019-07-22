<?php

namespace Encomage\Catalog\Setup;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;

/**
 * Class UpgradeData.
 */
class UpgradeData implements UpgradeDataInterface
{
    private $eavSetupFactory;

    /**
     * UpgradeData constructor.
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface   $context
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Validate_Exception
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Product::ENTITY,
                'ask_about_shoe_size',
                [
                    'group' => 'Product Details',
                    'type' => 'int',
                    'label' => 'Ask about shoe size',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_STORE,
                    'default' => 0,
                    'visible' => 1,
                    'required' => false,
                    'user_defined' => 1,
                    'searchable' => false,
                    'filterable' => true,
                    'comparable' => true,
                    'visible_on_front' => true,
                    'used_in_product_listing' => false,
                    'unique' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Product::ENTITY,
                'clearance',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'label' => 'Clearance',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.4', '<')) {
            $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
            $eavSetup->addAttribute(
                Product::ENTITY,
                'worn_these_shoes_before',
                [
                    'group' => 'General',
                    'type' => 'int',
                    'label' => 'Display question regarding shoe size?',
                    'input' => 'boolean',
                    'source' => Boolean::class,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => 0,
                    'searchable' => true,
                    'filterable' => true,
                    'comparable' => false,
                    'visible_on_front' => true,
                    'used_in_product_listing' => true,
                    'unique' => false,
                ]
            );
        }

        if (version_compare($context->getVersion(), '0.0.5', '<')) {
            $this->removeDuplicateAtt();
        }

        if (version_compare($context->getVersion(), '0.0.7', '<')) {
            $setup->startSetup();
            $this->removeSizeOptionValueForEnStore($setup);
            $setup->endSetup();
        }
    }

    private function removeDuplicateAtt()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->removeAttribute(Product::ENTITY, 'worn_these_shoes_before');
    }

    /**
     * @param $setup
     */
    private function removeSizeOptionValueForEnStore($setup)
    {
        $table = $setup->getTable('eav_attribute_option_value');
        $where = ['store_id = ?' => 1];
        $setup->getConnection()->delete($table, $where);
    }
}
