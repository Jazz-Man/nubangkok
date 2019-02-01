<?php

namespace Encomage\ProductNotification\Setup;

use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Encomage\ProductNotification\Setup
 */
class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{
    const TABLE_NAME = "encomage_product_notification";

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (!$installer->tableExists(self::TABLE_NAME)) {
            $table = $installer->getConnection()->newTable(
                $installer->getTable(self::TABLE_NAME)
            )
                ->addColumn(
                    'notification_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary'  => true,
                        'unsigned' => true,
                    ],
                    'Notification ID'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable => false'],
                    'Customer Email'
                )
                ->addColumn(
                    'product_id',
                    Table::TYPE_INTEGER,
                    10,
                    [],
                    'Product Id'
                )
                ->addColumn(
                    'product_name',
                    Table::TYPE_TEXT,
                    255,
                    [],
                    'Product Name'
                );
            $installer->getConnection()->createTable($table);
        }
        $installer->endSetup();
    }
}