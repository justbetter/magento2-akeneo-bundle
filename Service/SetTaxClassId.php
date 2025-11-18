<?php
declare(strict_types=1);

namespace JustBetter\AkeneoBundle\Service;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;

class SetTaxClassId
{
    /**
     * @var array<int, string>
     */
    protected array $taxIdColumns = [];

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
        if (!$this->scopeConfig->getValue('akeneo_connector/justbetter/settaxclass', ScopeInterface::SCOPE_WEBSITE) ||
            !($attributes = $this->scopeConfig->getValue(ConfigHelper::ATTRIBUTE_TYPES)) ||
            !($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))
        ) {
            return;
        }

        $unserializedAttributes = $this->serializer->unserialize($attributes);
        $unserializedMappings = $this->serializer->unserialize($mappings);
        
        if (!is_array($unserializedAttributes) || !is_array($unserializedMappings)) {
            return;
        }

        $this->taxIdColumns = [];
        foreach ($unserializedAttributes as $attribute) {
            if (is_array($attribute) && isset($attribute['magento_type']) && $attribute['magento_type'] === "tax") {
                $this->taxIdColumns[] = $attribute['pim_type'];
            }
        }

        if (empty($this->taxIdColumns) || empty($unserializedMappings)) {
            return;
        }

        $tmpTable = $this->entitiesHelper->getTableName($code);
        $taxColumns = $this->checkTaxColumnsExist($this->taxIdColumns, $tmpTable);

        if (empty($taxColumns)) {
            return;
        }

        $connection = $this->entitiesHelper->getConnection();

        foreach ($taxColumns as $taxIdColumn) {
            try {
                $taxQuery = $this->createQuery($taxIdColumn, $tmpTable);
                $connection->query($taxQuery);
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    protected function createQuery(string $taxIdColumn, string $tableName): string
    {
        $query = "UPDATE `" . $tableName . "` SET `" . $taxIdColumn . "` = ";

        return $this->addCase($query, $taxIdColumn);
    }

    protected function addCase(string $query, string $taxIdColumn): string
    {
        if (!($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))) {
            return $query;
        }
        $unserializedMappings = $this->serializer->unserialize($mappings);

        if (!is_array($unserializedMappings) || !count($unserializedMappings)) {
            return $query;
        }

        $query .= "CASE ";

        foreach ($unserializedMappings as $mapping) {
            if (!is_array($mapping)) {
                continue;
            }
            $query .= "WHEN `" . $taxIdColumn . "` = '" . $mapping['akeneo'] . "' then '" . $mapping['magento'] . "' ";
        }

        $query .= 'END';

        return $query;
    }

    /**
     * @param array<int, string> $mappings
     * @return array<int, string>
     */
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
