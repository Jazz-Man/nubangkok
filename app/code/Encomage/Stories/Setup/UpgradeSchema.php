<?php
namespace Encomage\Stories\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Encomage\Stories\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->addCustomerFullNameColumn($setup);
        }
        if (version_compare($context->getVersion(), '0.0.3', '<')) {
            $this->addTitleColumn($setup);
        }

        $setup->endSetup();
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addCustomerFullNameColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('encomage_stories'),
            'customer_name',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 128,
                'nullable' => false,
                'comment' => 'Customer Name',
                'after' => 'customer_id'
            ]
        );
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function addTitleColumn(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('encomage_stories'),
            'title',
            [
                'type' => Table::TYPE_TEXT,
                'length' => 256,
                'nullable' => false,
                'comment' => 'Story Title',
                'after' => 'entity_id'
            ]
        );
    }
}
