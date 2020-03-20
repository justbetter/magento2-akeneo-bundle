<?php

namespace JustBetter\AkeneoBundle\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class Uninstall implements UninstallInterface
{
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->getConnection()->startSetup();

        $setup->getConnection()
            ->dropColumn(
                $setup->getTable('eav_attribute'),
                'unit'
            );

        $setup->getConnection()
            ->dropColumn(
                $setup->getTable('eav_attribute'),
                'unit_conversion'
            );

        $setup->getConnection()->endSetup();
    }
}
