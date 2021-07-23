<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Job\Product as Subject;
use Magento\Catalog\Model\ProductRepository;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface as scope;

class EnableManageStock
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
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/enablemanagestock', scope::SCOPE_WEBSITE);

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

    /**
     * @throws CouldNotSaveException|NoSuchEntityException
     */
    protected function updateStockInfo(array $product): void
    {
        $product = $this->productRepository->get($product['identifier']);

        $stock = $product->getExtensionAttributes()->getStockItem();

        $stock
            ->setManageStock(1)
            ->setUseConfigManageStock(1);

        $this->stockItemRepository->save($stock);
    }

    /** Get the products from the temp table with a join to get the _entity_id */
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
