<?php
namespace Encomage\Stories\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class InstallSchema
 * @package Encomage\Stories\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $table = $setup->getConnection()
            ->newTable($setup->getTable('encomage_stories'))
            ->addColumn('entity_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Story Id'
            )->addColumn('customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null,
                ['unsigned' => true, 'nullable' => false],
                'Customer Id'
            )->addColumn('image_path', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255,
                ['nullable' => true],
                'Path to story image'
            )->addColumn('content', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, \Magento\Framework\DB\Ddl\Table::MAX_TEXT_SIZE,
                ['nullable' => false],
                'Story content'
            )->addColumn('is_approved', \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN, null,
                ['nullable' => false, 'default' => 0],
                'Story Status'
            )->addColumn('created_at', \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME, null,
                ['nullable' => false],
                'Story Created At (GMT)'
            );
        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}