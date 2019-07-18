<?php
namespace Encomage\Careers\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * Class UpgradeSchema
 *
 * @package Encomage\Careers\Setup
 */
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
                    'type' => Table::TYPE_TEXT,
                    'length' => Table::MAX_TEXT_SIZE,
                    'nullable' => false
                ]
            )->modifyColumn(
                $setup->getTable('encomage_careers'),
                'skills',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => Table::MAX_TEXT_SIZE,
                    'nullable' => false
                ]
            );
        }
    }
}
