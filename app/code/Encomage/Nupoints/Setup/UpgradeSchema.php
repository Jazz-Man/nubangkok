<?php

namespace Encomage\Nupoints\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class UpgradeSchema
 * @package Encomage\Nupoints\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface   $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $this->installHistoryTable($setup);
        }
    }

    /**
     * @param SchemaSetupInterface $setup
     */
    private function installHistoryTable(SchemaSetupInterface $setup)
    {
        $setup->getConnection()->addColumn(
            $setup->getTable('sales_order'),
            'nupoints',
            [
                'type' => Table::TYPE_INTEGER,
                'comment' => 'Numbers of nupoints'
            ]
        );
    }
}
