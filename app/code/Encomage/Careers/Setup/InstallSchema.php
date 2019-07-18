<?php

namespace Encomage\Careers\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * Class InstallSchema
 * @package Encomage\Careers\Setup
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
            ->newTable($setup->getTable('encomage_careers'))
            ->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Career Id'
            )->addColumn(
                'status',
                Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false],
                'Career Status'
            )->addColumn(
                'title',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Career Title'
            )->addColumn(
                'position',
                Table::TYPE_INTEGER,
                10,
                ['nullable' => false],
                'Career Position'
            )->addColumn(
                'short_description',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Career Description'
            )->addColumn(
                'skills',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Career Skills'
            )->addColumn(
                'recipient_email',
                Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Career Email'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            );
        $setup->getConnection()->createTable($table);
        $setup->endSetup();
    }
}
