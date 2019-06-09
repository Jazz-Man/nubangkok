<?php

namespace Encomage\DropdownFields\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class UpgradeSchema
 * @package Encomage\DropdownFields\Setup
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '0.1.2', '<')) {
            $table = $setup->getConnection()
                ->newTable($setup->getTable('encomage_country_region_city_table'))
                ->addColumn(
                    'entity_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ],
                    'Entity ID'
                )
                ->addColumn(
                    'country_code',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Country Code'
                )
                ->addColumn(
                    'country_name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Country Name'
                )
                ->addColumn(
                    'region',
                    Table::TYPE_TEXT,
                    '255',
                    [],
                    'Region'
                )->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    '255',
                    [],
                    'City'
                );
            $setup->getConnection()->createTable($table);
            $setup->endSetup();
        }
    }
}