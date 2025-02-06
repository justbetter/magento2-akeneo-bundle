<?php

namespace JustBetter\AkeneoBundle\Observer;

use Akeneo\Connector\Executor\JobExecutor;
use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Helper\Output;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class RemoveRedundantEav implements ObserverInterface
{
    public const CATALOG_PRODUCT_ENTITY_DATA_TYPES = ['int', 'text', 'decimal', 'gallery', 'varchar', 'datetime'];

    public function __construct(
        protected Entities $entities,
        protected ResourceConnection $resourceConnection,
        protected Output $outputHelper,
        protected JobExecutor $jobExecutor,
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    public function execute(Observer $observer): void
    {
        if (!$this->scopeConfig->isSetFlag('akeneo_connector/justbetter/remove_redundant_eav')) {
            return;
        }

        $this->jobExecutor->displayInfo((string)$this->outputHelper->getPrefix() . __('Remove Redundant EAV attribute values'));
        $connection = $this->resourceConnection->getConnection();

        foreach (self::CATALOG_PRODUCT_ENTITY_DATA_TYPES as $dataType) {
            $query = "
                DELETE cpe{$dataType}
                FROM catalog_product_entity_{$dataType} as cpe{$dataType}
                LEFT JOIN catalog_product_entity as cpe on cpe.entity_id = cpe{$dataType}.entity_id
                LEFT JOIN eav_attribute as ea on ea.attribute_id = cpe{$dataType}.attribute_id
                WHERE cpe{$dataType}.store_id = 0 AND cpe.attribute_set_id NOT IN (
                    SELECT attribute_set_id 
                    FROM eav_entity_attribute 
                    WHERE attribute_set_id = cpe.attribute_set_id AND attribute_id = cpe{$dataType}.attribute_id
                )
            ";
            $connection->query($query);
        }
    }
}
