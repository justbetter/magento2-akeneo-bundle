<?php

namespace JustBetter\AkeneoBundle\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->startSetup();

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('eav_attribute'),
                'unit',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'length'   => 255,
                    'nullable' => true,
                    'comment'  => 'UNIT',
                ]
            );

        $setup->getConnection()
            ->addColumn(
                $setup->getTable('eav_attribute'),
                'unit_conversion',
                [
                    'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                    'length'   => 11,
                    'nullable' => true,
                    'comment'  => 'UNIT CONVERSION',
                ]
            );

        $setup->getConnection()->endSetup();
    }
}
