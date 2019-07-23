<?php
/**
 * Easyship.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Easyship.com license that is
 * available through the world-wide-web at this URL:
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Goeasyship
 * @package     Goeasyship_Shipping
 * @copyright   Copyright (c) 2018 Easyship (https://www.easyship.com/)
 * @license     https://www.apache.org/licenses/LICENSE-2.0
 */

namespace Goeasyship\Shipping\Setup;

use Goeasyship\Shipping\Model\Source\Categories;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Class InstallData
 *
 * @package Goeasyship\Shipping\Setup
 */
class InstallData implements InstallDataInterface
{

    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $_eavSetupFactory;

    /**
     * InstallData constructor.
     *
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->_eavSetupFactory = $eavSetupFactory;
    }

    /**
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface   $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $eavSetup = $this->_eavSetupFactory->create(['setup' => $setup]);
        $easyship_height_data = [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Easyship Height (cm)',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
        ];
        $this->createProductAttribute($eavSetup, 'easyship_height', $easyship_height_data);

        $easyship_width_data = [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Easyship Width (cm)',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
        ];
        $this->createProductAttribute($eavSetup, 'easyship_width', $easyship_width_data);

        $easyship_length_data = [
            'type' => 'int',
            'backend' => '',
            'frontend' => '',
            'label' => 'Easyship Length (cm)',
            'input' => 'text',
            'class' => '',
            'source' => '',
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
        ];
        $this->createProductAttribute($eavSetup, 'easyship_length', $easyship_length_data);

        $easyship_category_data = [
            'type' => 'text',
            'backend' => ArrayBackend::class,
            'frontend' => '',
            'label' => 'Easyship Category',
            'input' => 'select',
            'class' => '',
            'source' => Categories::class,
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible' => true,
            'required' => false,
            'user_defined' => false,
            'default' => '',
            'searchable' => false,
            'filterable' => false,
            'comparable' => false,
            'visible_on_front' => false,
            'used_in_product_listing' => true,
            'unique' => false,
            'apply_to' => ''
        ];
        $this->createProductAttribute($eavSetup, 'easyship_category', $easyship_category_data);

        $setup->endSetup();
    }

    /**
     * @param $eavSetup
     * @param $code
     * @param $data
     */
    protected function createProductAttribute($eavSetup, $code, $data)
    {
        $eavSetup->addAttribute(Product::ENTITY, $code, $data);
    }
}
