<?php

namespace Encomage\P2c2pPayment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * Class InstallSchema
 * @package Encomage\P2c2pPayment\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $setup->getConnection()->changeColumn(
            $setup->getTable('p2c2p_meta'),
            'p2c2p_id',
            'p2c2p_id',
            [
                'type' => Table::TYPE_BIGINT,
                'length' => 20,
                'auto_increment' => true,
                'nullable' => false,
                'unsigned' => true,
                'comment' => 'P2c2p_id'
            ]
        );

        $installer->endSetup();
    }
}