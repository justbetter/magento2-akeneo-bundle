<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Job\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\ScopeInterface as Scope;

class EnableManageStock
{
    protected ResourceConnection|AdapterInterface $connection;

    public function __construct(
        protected ProductImportHelper $entitiesHelper,
        protected ScopeConfigInterface $config
    ) {
        $this->connection = $this->entitiesHelper->getConnection();
    }

    public function afterInitStock(Product $subject): bool
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/enablemanagestock', Scope::SCOPE_WEBSITE);

        if (! $extensionEnabled) {
            return true;
        }

        $products = $this->getProducts($subject);
        if(!empty($products)) {
            $connection = $this->entitiesHelper->getConnection();
            $where = ['product_id' . ' IN(?)' => [$products]];
            $connection->update($this->entitiesHelper->getTable('cataloginventory_stock_item'), ['manage_stock' => '1','use_config_manage_stock' => '1'], $where);
        }
        return true;
    }

    protected function getProducts(Product $subject): array
    {
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $query = $this->connection->select()->from(['t' => $tmpTableName],['c.entity_id'])->joinInner(
            ['c' => 'catalog_product_entity'],
            't.identifier = c.sku'
        );

        return $this->connection->fetchPairs($query);
    }
}
