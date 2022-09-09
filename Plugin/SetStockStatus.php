<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Job\Product as Subject;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\ScopeInterface as Scope;

class SetStockStatus
{
    /** @var ProductImportHelper */
    protected $entitiesHelper;

    /** @var AdapterInterface */
    protected $connection;

    /** @var ScopeConfigInterface */
    protected $config;

    public function __construct(
        ProductImportHelper $entitiesHelper,
        ScopeConfigInterface $config
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->config = $config;

        $this->connection = $this->entitiesHelper->getConnection();
    }

    public function afterInitStock(Subject $subject): bool
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/setstockstatus', Scope::SCOPE_WEBSITE);

        if (! $extensionEnabled) {
            return true;
        }

        $products = $this->getProducts($subject);
        if(!empty($products)) {
            $connection = $this->entitiesHelper->getConnection();
            $where = ['product_id' . ' IN(?)' => [$products], 'backorders' . ' IN(?)' => [1, 2]];
            $connection->update($this->entitiesHelper->getTable('cataloginventory_stock_item'), ['is_in_stock' => '1'], $where);
        }
        return true;
    }

    protected function getProducts(Subject $subject): array
    {
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $query = $this->connection->select()->from(['t' => $tmpTableName],['c.entity_id'])->joinInner(
            ['c' => 'catalog_product_entity'],
            't.identifier = c.sku'
        );

        return $this->connection->fetchPairs($query);
    }
}
