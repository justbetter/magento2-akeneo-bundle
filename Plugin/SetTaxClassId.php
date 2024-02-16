<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
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
    protected $entitiesHelper;
    protected $storeHelper;
    protected $serializer;
    protected $configHelper;
    protected $authenticator;
    protected $scopeConfig;
    protected $tax_id_columns;

    /**
     * @param ProductImportHelper  $entitiesHelper
     * @param StoreHelper          $storeHelper
     * @param Json                 $serializer
     * @param ConfigHelper         $configHelper
     * @param Authenticator        $authenticator
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ProductImportHelper $entitiesHelper,
        StoreHelper $storeHelper,
        Json $serializer,
        ConfigHelper $configHelper,
        Authenticator $authenticator,
        ScopeConfigInterface $scopeConfig,
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->storeHelper = $storeHelper;
        $this->serializer = $serializer;
        $this->configHelper = $configHelper;
        $this->authenticator = $authenticator;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Overwrite Magento Tax Class with the one from Akeneo.
     *
     * @param Product $context
     */
    public function afterAddRequiredData(Product $context)
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

        $this->tax_id_columns = [];
        foreach ($attributes as $attribute) {
            if ($attribute['magento_type'] === "tax") {
                $this->tax_id_columns[] = $attribute['pim_type'];
            }
        }

        if (!$this->tax_id_columns || !count($mappings)) {
            return;
        }

        $tmpTable = $this->entitiesHelper->getTableName($context->getCode());

        $taxColumns = $this->checkTaxColumnsExist($this->tax_id_columns, $tmpTable);

        if (empty($taxColumns)) {
            return;
        }
    }

    /**
     * Before UpdateOption - Map Akeneo Tax Class option to Magento counterpart
     *
     * @param $subject
     * @return array
     */
    public function beforeUpdateOption($subject)
    {
        if (!$this->tax_id_columns) {
            return [$subject];
        }

        $connection = $this->entitiesHelper->getConnection();
        $tmpTable = $this->entitiesHelper->getTableName($subject->getCode());

        if ($taxColumns = $this->checkTaxColumnsExist($this->tax_id_columns, $tmpTable)) {
            foreach ($taxColumns as $tax_id_column) {
                try {
                    $taxQuery = $this->createQuery($tax_id_column, $tmpTable);
                    $connection->query($taxQuery);
                } catch (Exception $e) {
                    throw $e;
                }
            }
        }

        return [$subject];
    }

    /**
     * Create the query to update the rows.
     *
     * @param string $tax_id_column
     * @param string $tableName
     *
     * @return string
     */
    public function createQuery($tax_id_column, $tableName)
    {
        $query = "
            UPDATE `" . $tableName . "`
            SET `".$tax_id_column."` =
            ";

        $query = $this->addCase($query, $tax_id_column);

        return $query;
    }

    /**
     * Add the switch case to the query.
     *
     * @param string $query
     * @param string $tax_id_column
     *
     * @return string
     */
    public function addCase($query, $tax_id_column)
    {
        if (!($mappings = $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping'))) {
            return ;
        }
        $mappings = $this->serializer->unserialize($mappings);

        if (!count($mappings)) {
            return $query;
        }

        $query .= "CASE
        ";

        foreach ($mappings as $mapping) {
            $query .= "WHEN `" . $tax_id_column . "` = '" . $mapping['akeneo'] . "' then '" . $mapping['magento'] . "'
            ";
        }

        $query .= 'END';

        return $query;
    }

    /**
     * Check If the Tax Class is localizable and exist
     *
     * @param $mappings
     * @param $tmpTable
     * @return array
     */
    public function checkTaxColumnsExist($mappings, $tmpTable)
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
