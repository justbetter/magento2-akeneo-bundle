<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Job\Product as Subject;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\ScopeInterface as Scope;

class SetStockStatus
{
    /** @var ProductImportHelper */
    protected $entitiesHelper;

    /** @var AdapterInterface */
    protected $connection;

    /** @var StockItemRepository */
    protected $stockItemRepository;

    /** @var ProductRepository */
    protected $productRepository;

    /** @var ScopeConfigInterface */
    protected $config;

    public function __construct(
        ProductImportHelper $entitiesHelper,
        StockItemRepository $stockItemRepository,
        ProductRepository $productRepository,
        ScopeConfigInterface $config
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->stockItemRepository = $stockItemRepository;
        $this->productRepository = $productRepository;
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

        foreach ($products as $product) {
            try {
                $this->updateStockInfo($product);
            } catch (\Exception $e) {
                continue;
            }
        }

        return true;
    }

    protected function updateStockInfo(array $product): void
    {
        $product = $this->productRepository->get($product['identifier']);

        $stock = $product->getExtensionAttributes()->getStockItem();

        if ($stock->getBackorders() === 1 || $stock->getBackorders() === 2) {
            $stock->setIsInStock(1);

            $this->stockItemRepository->save($stock);
        }
    }

    protected function getProducts(Subject $subject): array
    {
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $query = $this->connection->select()->from(['t' => $tmpTableName])->joinInner(
            ['c' => 'catalog_product_entity'],
            't.identifier = c.sku'
        );

        return $this->connection->fetchAll($query);
    }
}
