<?php

namespace Encomage\ErpIntegration\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 * @package Encomage\ErpIntegration\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * UpgradeData constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     */
    public function __construct(CustomerSetupFactory $customerSetupFactory)
    {
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            /** @var CustomerSetup $customerSetup */
            $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
            $customerSetup->addAttribute(
                Customer::ENTITY,
                'erp_customer_code',
                [
                    'type' => 'varchar',
                    'input' => 'text',
                    'label' => 'ERP Customer Code',
                    'required' => false,
                    'visible' => true,
                    'system' => 0,
                    'position' => 100,
                    'filterable' => true,
                ]
            );
            $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'erp_customer_code')
                ->setData('used_in_forms', ['customer_account_create'])
                ->save();
        }
    }
}

