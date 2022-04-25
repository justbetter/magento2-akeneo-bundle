<?php

namespace JustBetter\AkeneoBundle\Plugin;

use Akeneo\Connector\Job\Product;
use Akeneo\Connector\Helper\Config;
use Akeneo\Connector\Helper\Authenticator;
use Akeneo\Connector\Helper\Store as StoreHelper;
use Akeneo\Connector\Helper\Config as ConfigHelper;
use Akeneo\Connector\Helper\Import\Product as ProductImportHelper;
use Magento\Store\Model\ScopeInterface as scope;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Eav\Model\Config as EavConfig;

class SetTaxClassId
{
    protected $entitiesHelper;
    protected $configHelper;
    protected $scopeConfig;
    protected $tax_id_columns;
    protected $storeHelper;
    protected $serializer;

    /**
     * @param ProductImportHelper  $entitiesHelper
     * @param StoreHelper          $storeHelper
     * @param Json                 $serializer
     * @param ConfigHelper         $configHelper
     * @param Authenticator        $authenticator
     * @param ScopeConfigInterface $scopeConfig
     * @param EavConfig            $eavConfig
     */
    public function __construct(
        ProductImportHelper $entitiesHelper,
        StoreHelper $storeHelper,
        Json $serializer,
        ConfigHelper $configHelper,
        Authenticator $authenticator,
        ScopeConfigInterface $scopeConfig,
        EavConfig $eavConfig
    ) {
        $this->entitiesHelper = $entitiesHelper;
        $this->storeHelper = $storeHelper;
        $this->serializer = $serializer;
        $this->configHelper = $configHelper;
        $this->authenticator = $authenticator;
        $this->scopeConfig = $scopeConfig;
        $this->eavConfig = $eavConfig;
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

        $attributes = $this->serializer->unserialize(
            $this->scopeConfig->getValue(ConfigHelper::ATTRIBUTE_TYPES)
        );

        $mappings = $this->serializer->unserialize(
            $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping')
        );

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

        foreach ($taxColumns as $tax_id_column) {
            $taxQuery = $this->createQuery($tax_id_column, $tmpTable);
            if (!$taxQuery) {
                return;
            }
            try {
                $connection = $this->entitiesHelper->getConnection();
                $connection->query($taxQuery);
            } catch (Exception $e) {
                throw $e;
            }
        }
    }

    /**
     * After tax_class_id has been set to normal option value
     * set it to correct value.
     *
     * @param Product $context
     * @return void
     */
    public function afterUpdateOption(Product $context)
    {
        if (!$this->tax_id_columns) {
            return;
        }

        /** @var AdapterInterface $connection */
        $connection = $this->entitiesHelper->getConnection();
        /** @var string $tmpTable */
        $tmpTable = $this->entitiesHelper->getTableName($context->getCode());

        $taxColumns = $this->checkTaxColumnsExist($this->tax_id_columns, $tmpTable);

        foreach ($taxColumns as $taxColumn) {
            $connection->query('UPDATE ' . $tmpTable . ' SET `' . $taxColumn . '` = `_tax_class_id`;');
        }
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
            SET `_tax_class_id` =
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
        $mappings = $this->serializer->unserialize(
            $this->scopeConfig->getValue('akeneo_connector/product/tax_id_mapping')
        );

        if (!count($mappings)) {
            return $query;
        }

        $query .= "CASE
        ";

        foreach ($mappings as $mapping) {
            $query .= "WHEN `" . $tax_id_column . "` = '" . $mapping['akeneo'] . "' then '" . $mapping['magento'] . "'
            ";
        }

        $query .= 'ELSE `_tax_class_id`
        END';

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

        foreach ($mappings as $key => $mapping) {

            $akeneoAttribute = $this->authenticator->getAkeneoApiClient()->getAttributeApi()->get($mapping);

            if($akeneoAttribute['localizable'] === false) {
                if ($connection->tableColumnExists($tmpTable, $mapping)) {
                    $newMappings[] = $mapping;
                }
            }

            if (isset($akeneoAttribute['localizable'])) {
                $mappedChannels = $this->configHelper->getMappedChannels();
                foreach ($mappedChannels as $key => $channel) {
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
