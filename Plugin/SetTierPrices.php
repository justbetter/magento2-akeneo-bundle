<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Exception;
use Zend_Db_Expr as Expr;
use Akeneo\Connector\Job\Product;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;

/**
 * SetTierPrices class
 */
class SetTierPrices
{
    protected mixed $customerGroups;
    public string $customerGroupsUnserialized;

    public function __construct(
        protected Json $serializer,
        protected ScopeConfigInterface $config,
        protected ProductImportHelper $entitiesHelper
    ) {
    }

    /**
     * @throws Exception
     */
    public function afterImportMedia(Product $subject, bool $result): bool
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/tierprices', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        $this->customerGroups = $this->config->getValue('akeneo_connector/product/groups', scope::SCOPE_WEBSITE);
        $this->customerGroupsUnserialized = $this->serializer->unserialize($this->customerGroups ?? '{}');

        $tmpTableName = $this->entitiesHelper->getTableName($subject->getCode());
        $connection = $this->entitiesHelper->getConnection();
        $this->removeTierPrices();
        $connection->beginTransaction();
        $this->setTierPrices($tmpTableName);

        try {
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollBack();
            throw $e;
        }

        return $result;
    }

    /**
     * Remove tier prices function
     * Remove all tier prices that match with the tmp table
     */
    public function removeTierPrices(): void
    {
        $connection = $this->entitiesHelper->getConnection();
        $connection->query(
            "
            DELETE cpetp FROM `catalog_product_entity_tier_price` AS cpetp
            INNER JOIN `tmp_akeneo_connector_entities_product` AS tacep
            ON cpetp.`entity_id` = tacep.`_entity_id`
            "
        );
    }

    /**
     * Set tier prices function
     */
    public function setTierPrices(string $tmpTableName): void
    {
        foreach ($this->customerGroups as $option) {
            $connection = $this->entitiesHelper->getConnection();
            $exist = $connection->tableColumnExists($tmpTableName, $option['pim_type']);
            if ($exist) {
                $select = $connection->select()
                ->from(
                    $tmpTableName,
                    [
                        'value_id' => new Expr("'" . null . "'"),
                        'entity_id' => '_entity_id',
                        'all_groups' => new Expr("'" . 0 . "'"),
                        'customer_group_id' => new Expr("'" . $option['magento_type'] . "'"),
                        'qty' => new Expr("'" . 1 . "'"),
                        'value' => $option['pim_type'],
                        'website_id' => new Expr("'" . 0 . "'"),
                        'percentage_value' => new Expr("NULL"),
                    ]
                )->where("`" . $option['pim_type'] . "`" . ' != ?', ['notnull' => true]);

                $connection->query(
                    $connection->insertFromSelect(
                        $select,
                        'catalog_product_entity_tier_price',
                        [
                            'value_id',
                            'entity_id',
                            'all_groups',
                            'customer_group_id',
                            'qty',
                            'value',
                            'website_id',
                            'percentage_value'
                        ],
                        2
                    )
                );
            }
        }
    }
}
