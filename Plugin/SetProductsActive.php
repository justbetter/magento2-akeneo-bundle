<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Job\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\ScopeInterface as Scope;

class SetProductsActive
{
    protected const CONFIG_KEY = 'akeneo_connector/justbetter/setproductsactive';
    protected AdapterInterface $connection;

    public function __construct(
        protected ScopeConfigInterface $config,
        protected Entities $entitiesHelper,
        protected Attribute $attribute
    ) {
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
        $ids = array_map(fn($product) => $product['_entity_id'], $products);

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
