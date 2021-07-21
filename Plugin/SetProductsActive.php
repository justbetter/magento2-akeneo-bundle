<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Import\Entities;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as catalogProduct;

class SetProductsActive
{
    protected $config;
    protected $entitiesHelper;

    /**
     * __construct function
     * @param ScopeConfigInterface $config
     * @param Entities $entitiesHelper
     */
    public function __construct(
        ScopeConfigInterface $config,
        Entities $entitiesHelper,
        ProductRepositoryInterface $productRepository
    ) {
        $this->config = $config;
        $this->entitiesHelper = $entitiesHelper;
        $this->productRepository = $productRepository;
        $this->connection = $this->entitiesHelper->getConnection();
    }

    /**
     * afterInsertData function
     * @param  product $subject
     * @param  bool $result
     * @return bool $result
     */
    public function afterInsertData(product $subject, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/setproductsactive', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $products = $this->getProducts($subject);

        foreach ($products as $product) {
            try {
                $this->update($product);
            } catch (CouldNotSaveException $e) {
                // TODO: possible slack message?
            } catch (NoSuchEntityException $e) {
            }
        }

        return $result;
    }

    /**
     * Get the products from the temp table with a join to get the _entity_id
     *
     * @param product $subject
     * @return array
     */
    protected function getProducts(product $subject): array
    {
        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $query = $this->connection->select()->from(['t' => $tmpTableName])->joinInner(
            ['c' => 'catalog_product_entity'],
            't.identifier = c.sku'
        );
        return $this->connection->fetchAll($query);
    }

    /**
     * @param $identifier
     * @return ProductInterface|catalogProduct|null
     * @throws NoSuchEntityException
     */
    protected function getProduct(string $identifier): catalogProduct
    {
        return $this->productRepository->get($identifier);
    }

    /**
     * update the stock information
     *
     * @param $product
     * @return void
     * @throws CouldNotSaveException|NoSuchEntityException
     */
    protected function update(array $product): void
    {
        $product = $this->getProduct($product['identifier']);

        if (!$product) {
            return;
        }

        $product->setStatus(Status::STATUS_ENABLED);
        $product = $this->productRepository->save($product);
    }
}
