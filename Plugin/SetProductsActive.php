<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Import\Entities;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface as Scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as CatalogProduct;

class SetProductsActive
{
    protected const CONFIG_KEY = 'akeneo_connector/justbetter/setproductsactive';

    protected ScopeConfigInterface $config;
    protected Entities $entitiesHelper;
    protected ProductRepositoryInterface $productRepository;
    protected AdapterInterface $connection;
    protected Attribute $attribute;

    public function __construct(
        ScopeConfigInterface $config,
        Entities $entitiesHelper,
        ProductRepositoryInterface $productRepository,
        Attribute $attribute
    ) {
        $this->config = $config;
        $this->entitiesHelper = $entitiesHelper;
        $this->productRepository = $productRepository;
        $this->attribute = $attribute;
        $this->connection = $this->entitiesHelper->getConnection();
    }

    public function afterInitStock(Product $subject, $result)
    {
        if (!$this->config->getValue(static::CONFIG_KEY, Scope::SCOPE_WEBSITE)) {
            return $result;
        }

        $products = $this->getProducts($subject);

        $this->update($products);

        return $result;
    }

    protected function getProducts(Product $subject): array
    {
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());

        $query = $this->connection
            ->select()
            ->from(['t' => $tmpTableName])
            ->joinInner(
                ['c' => 'catalog_product_entity'],
                't.identifier = c.sku'
            );

        return $this->connection->fetchAll($query);
    }

    protected function update(array $products): void
    {
        $ids = array_map(function ($product) {
            return $product['_entity_id'];
        }, $products);

        $table = $this->connection->getTableName('catalog_product_entity_int');
        $attributeId = $this->attribute->getIdByCode('catalog_product', 'status');

        foreach (array_chunk($ids, 100) as $chunk) {
            $this->connection->update(
                $table,
                [
                    'value' => Status::STATUS_ENABLED
                ],
                [
                    'attribute_id = ' . $attributeId
                    . ' AND entity_id IN (' . implode(',', $chunk) . ')'
                    . ' AND value <> ' . Status::STATUS_ENABLED
                    . ' AND store_id = 0'
                ]
            );
        }
    }
}
