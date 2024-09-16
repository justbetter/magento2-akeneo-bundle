<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Akeneo\Connector\Job\Product;
use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface as scope;

class SetTaxClassId
{
    protected ?array $taxIdColumns = null;

    public function __construct(
        protected ProductImportHelper $entitiesHelper,
        protected StoreHelper $storeHelper,
        protected Json $serializer,
        protected ConfigHelper $configHelper,
        protected Authenticator $authenticator,
        protected ScopeConfigInterface $scopeConfig,
    ) {
    }

    /**
     * Overwrite Magento Tax Class with the one from Akeneo.
     */
    public function afterAddRequiredData(Product $context): void
    {
        $extensionEnabled = $this->scopeConfig->getValue('akeneo_connector/justbetter/settaxclass', scope::SCOPE_WEBSITE);
        if (!$extensionEnabled) {
            return ;
        }

        if (
            !($attributes = $this->scopeConfig->getValue(ConfigHelper::ATTRIBUTE_TYPES)) ||
            !($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))) {
            return ;
        }

        $attributes = $this->serializer->unserialize($attributes);

        $mappings = $this->serializer->unserialize($mappings);

        $this->taxIdColumns = [];
        foreach ($attributes as $attribute) {
            if ($attribute['magento_type'] === "tax") {
                $this->taxIdColumns[] = $attribute['pim_type'];
            }
        }

        if (!$this->taxIdColumns || !(is_countable($mappings) ? count($mappings) : 0)) {
            return;
        }

        $tmpTable = $this->entitiesHelper->getTableName($context->getCode());

        $taxColumns = $this->checkTaxColumnsExist($this->taxIdColumns, $tmpTable);

        if (empty($taxColumns)) {
            return;
        }
    }

    /**
     * Map Akeneo Tax Class option to Magento counterpart
     * @throws Exception
     */
    public function beforeUpdateOption(Product $subject): array
    {
        if (!$this->taxIdColumns) {
            return [$subject];
        }

        $connection = $this->entitiesHelper->getConnection();
        $tmpTable = $this->entitiesHelper->getTableName($subject->getCode());

        if ($taxColumns = $this->checkTaxColumnsExist($this->taxIdColumns, $tmpTable)) {
            foreach ($taxColumns as $taxIdColumn) {
                $taxQuery = $this->createQuery($taxIdColumn, $tmpTable);
                $connection->query($taxQuery);
            }
        }

        return [$subject];
    }

    /**
     * Create the query to update the rows.
     */
    public function createQuery(string $taxIdColumn, string $tableName): string
    {
        $query = "
            UPDATE `" . $tableName . "`
            SET `".$taxIdColumn."` =
            ";

        return $this->addCase($query, $taxIdColumn);
    }

    /**
     * Add the switch case to the query.
     */
    public function addCase(string $query, string $taxIdColumn): ?string
    {
        if (!($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))) {
            return $query;
        }

        $mappings = $this->serializer->unserialize($mappings);

        if (!(is_countable($mappings) ? count($mappings) : 0)) {
            return $query;
        }

        $query .= "CASE
        ";

        foreach ($mappings as $mapping) {
            $query .= "WHEN `" . $taxIdColumn . "` = '" . $mapping['akeneo'] . "' then '" . $mapping['magento'] . "'
            ";
        }

        $query .= 'END';

        return $query;
    }

    /**
     * Check If the Tax Class is localizable and exist
     *
     * @throws Exception
     */
    public function checkTaxColumnsExist(array $mappings, string $tmpTable): array
    {
        $newMappings = [];

        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();

        foreach ($mappings as $mapping) {

            $akeneoAttribute = $this->authenticator->getAkeneoApiClient()->getAttributeApi()->get($mapping);

            if($akeneoAttribute['localizable'] === false) {
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
