<?php
namespace Encomage\Careers\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.0.2', '<')) {
            $setup->getConnection()->modifyColumn(
                $setup->getTable('encomage_careers'),
                'short_description',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                    'nullable' => false
                ]
            )->modifyColumn(
                $setup->getTable('encomage_careers'),
                'skills',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length' => \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                    'nullable' => false
                ]
            );
        }
    }
}