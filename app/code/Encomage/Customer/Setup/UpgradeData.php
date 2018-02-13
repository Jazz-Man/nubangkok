<?php

namespace Encomage\Customer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

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
                'line_id',
                [
                    'type' => 'varchar',
                    'input' => 'text',
                    'label' => 'Line Id',
                    'required' => false,
                    'visible' => true,
                    'system' => 0,
                    'position' => 100,
                    'filterable' => true,
                ]
            );
            $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'line_id')
                ->setData('used_in_forms', ['customer_account_create'])
                ->save();
        }
    }
}

