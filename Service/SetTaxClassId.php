<?php

namespace JustBetter\AkeneoBundle\Service;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Exception;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;

class SetTaxClassId
{
    protected array $tax_id_columns = [];

    public function __construct(
        protected ProductImportHelper $entitiesHelper,
        protected StoreHelper $storeHelper,
        protected Json $serializer,
        protected ConfigHelper $configHelper,
        protected Authenticator $authenticator,
        protected ScopeConfigInterface $scopeConfig
    ) {
    }

    public function execute(string $code): void
    {
        $extensionEnabled = $this->scopeConfig->getValue('akeneo_connector/justbetter/settaxclass', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return;
        }

        if (
            !($attributes = $this->scopeConfig->getValue(ConfigHelper::ATTRIBUTE_TYPES)) ||
            !($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))
        ) {
            return;
        }

        $attributes = $this->serializer->unserialize($attributes);
        $mappings = $this->serializer->unserialize($mappings);

        $this->tax_id_columns = [];
        foreach ($attributes as $attribute) {
            if ($attribute['magento_type'] === "tax") {
                $this->tax_id_columns[] = $attribute['pim_type'];
            }
        }

        if (!$this->tax_id_columns || !count($mappings)) {
            return;
        }

        $tmpTable = $this->entitiesHelper->getTableName($code);
        $taxColumns = $this->checkTaxColumnsExist($this->tax_id_columns, $tmpTable);

        if (empty($taxColumns)) {
            return;
        }

        $connection = $this->entitiesHelper->getConnection();

        foreach ($taxColumns as $tax_id_column) {
            try {
                $taxQuery = $this->createQuery($tax_id_column, $tmpTable);
                $connection->query($taxQuery);
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    protected function createQuery(string $tax_id_column, string $tableName): string
    {
        $query = "UPDATE `" . $tableName . "` SET `" . $tax_id_column . "` = ";
        return $this->addCase($query, $tax_id_column);
    }

    protected function addCase(string $query, string $tax_id_column): string
    {
        if (!($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))) {
            return $query;
        }
        $mappings = $this->serializer->unserialize($mappings);

        if (!count($mappings)) {
            return $query;
        }

        $query .= "CASE ";

        foreach ($mappings as $mapping) {
            $query .= "WHEN `" . $tax_id_column . "` = '" . $mapping['akeneo'] . "' then '" . $mapping['magento'] . "' ";
        }

        $query .= 'END';

        return $query;
    }

    protected function checkTaxColumnsExist(array $mappings, string $tmpTable): array
    {
        $newMappings = [];
        $connection = $this->entitiesHelper->getConnection();

        foreach ($mappings as $mapping) {
            $akeneoAttribute = $this->authenticator->getAkeneoApiClient()->getAttributeApi()->get($mapping);

            if ($akeneoAttribute['localizable'] === false) {
                if ($connection->tableColumnExists($tmpTable, $mapping)) {
                    $newMappings[] = $mapping;
                }
            }

            if (isset($akeneoAttribute['localizable'])) {
                $mappedChannels = $this->configHelper->getMappedChannels();
                foreach ($mappedChannels as $channel) {
                    foreach ($this->storeHelper->getChannelStoreLangs($channel) as $locale) {
                        if ($connection->tableColumnExists($tmpTable, $mapping . '-' . $locale . '-' . $channel)) {
                            $newMappings[] = $mapping . '-' . $locale . '-' . $channel;
                        }
                    }
                }
            }
        }

        return $newMappings;
    }
}
