<?php
declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Service;

use Akeneo\Connector\Helper\Import\Entities;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\ScopeInterface;

class CategoryExist
{
    public function __construct(
        protected ScopeConfigInterface $config,
        protected Entities $entitiesHelper,
        protected StoreHelper $storeHelper,
        protected ResourceConnection $resourceConnection
    ) {
    }

    public function execute(): void
    {
        $extensionEnabled = $this->config->getValue('akeneo_connector/justbetter/categoryexist', ScopeInterface::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return;
        }

        $connection = $this->entitiesHelper->getConnection();
        $stores = $this->storeHelper->getStores('lang');
        $tmpTable = $this->entitiesHelper->getTableName('category');
        $varcharTable = $this->resourceConnection->getTableName('catalog_category_entity_varchar');
        $attributeTable = $this->resourceConnection->getTableName('eav_attribute');
        $entityTypeTable = $this->resourceConnection->getTableName('eav_entity_type');

        foreach ($stores as $local => $affected) {
            foreach ($affected as $store) {
                $columnName = 'url_key-' . $store['lang'];

                $query = "
                    UPDATE {$tmpTable} temp
                    LEFT JOIN {$varcharTable} eav ON (
                        eav.entity_id = temp._entity_id 
                        AND eav.attribute_id = (
                            SELECT attribute_id 
                            FROM {$attributeTable} 
                            WHERE attribute_code = 'url_key' 
                            AND entity_type_id = (
                                SELECT entity_type_id 
                                FROM {$entityTypeTable} 
                                WHERE entity_type_code = 'catalog_category'
                            )
                            AND eav.value != ''
                        )
                        AND eav.store_id = {$store['store_id']}
                    )
                    SET temp.`{$columnName}` = eav.value
                    WHERE temp._is_new = 0 AND eav.value IS NOT NULL
                ";

                $connection->query($query);
            }
        }
    }
}
