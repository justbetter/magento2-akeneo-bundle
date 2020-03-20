<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Exception;
use Zend_Db_Expr as Expr;
use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Serializer;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;

/**
 * SetTierPrices class
 */
class SetTierPrices
{
    protected $serializer;
    protected $config;
    protected $entitiesHelper;
    public $customerGroupsUnserialized;

    /**
     * construct function
     *
     * @param Serializer $serializer
     * @param ScopeConfigInterface $config
     * @param ProductImportHelper $entitiesHelper
     */
    public function __construct(
        Serializer $serializer,
        ScopeConfigInterface $config,
        ProductImportHelper $entitiesHelper
    ) {
        $this->serializer = $serializer;
        $this->config = $config;
        $this->entitiesHelper = $entitiesHelper;
    }

    /**
     * AfterImportMedia function
     *
     * @param product $subject
     * @param bool $result
     * @return bool $result
     */
    public function afterImportMedia(product $subject, $result)
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/tierprices', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return $result;
        }

        $this->customerGroups = $this->config->getValue('akeneo_connector/product/groups', scope::SCOPE_WEBSITE);
        $this->customerGroupsUnserialized = $this->serializer->unserialize($this->customerGroups);

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
     * Remove tierprices function
     * Remove all tier prices that match with the tmp table
     */
    public function removeTierPrices()
    {
        try {
            $connection = $this->entitiesHelper->getConnection();
            $connection->query(
                "
                DELETE cpetp FROM `catalog_product_entity_tier_price` AS cpetp
                INNER JOIN `tmp_akeneo_connector_entities_product` AS tacep
                ON cpetp.`entity_id` = tacep.`_entity_id`
                "
            );
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * set tierprices function
     *
     * @param string $tmpTableName
     * @return void
     */
    public function setTierPrices($tmpTableName)
    {
        foreach ($this->customerGroupsUnserialized as $option) {
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
