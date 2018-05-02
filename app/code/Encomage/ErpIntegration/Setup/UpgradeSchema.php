<?php
namespace Encomage\ErpIntegration\Setup;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeData
 * @package Encomage\ErpIntegration\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
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
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->addAttributeForCustomer($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addAttributeForCustomer(SchemaSetupInterface $setup)
    {
        /** @var \Magento\Customer\Setup\CustomerSetup $customerSetup */
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
                'position' => 150,
                'filterable' => true
            ]
        );
        $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'erp_customer_code')
            ->setData('used_in_forms', ['customer_account_create'])
            ->save();
    }
}

